<?php

namespace App\Helpers;

use Illuminate\Database\Query\Builder;

/**
 * Class Pagination
 *
 * Handles data pagination and sorting for DataTables.
 */
class Pagination
{
    /**
     * Get paginated data for DataTables.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  array  $postData  The request data from DataTables.
     * @return array The paginated response.
     */
    public function getDataTable(Builder $query, array $postData): array
    {
        $response = [];
        $result = $this->setDataTable($postData);
        $total = $query->count();

        $response['recordsTotal'] = $total ?? 0;

        // Apply ordering if provided
        if (! empty($result['orderByField'])) {
            $query->orderBy($result['orderByField'], $result['orderBy']);
        }

        // Set pagination limits and offsets
        $query->offset($result['offset'])->limit($result['limit']);

        $response['data'] = $query->get();
        $response['recordsFiltered'] = $response['recordsTotal'];
        $response['draw'] = $result['draw'];

        return $response;
    }

    /**
     * Set pagination and ordering data for DataTables.
     *
     * @param  array  $data  The request data from DataTables.
     * @return array Pagination and ordering configuration.
     */
    public function setDataTable(array $data): array
    {
        $paginationData = [
            'draw' => $data['draw'] ?? 0,
            'limit' => min($data['length'] ?? 20, 100),
            'offset' => $data['start'] ?? 0,
            'orderBy' => isset($data['order'][0]['dir']) && $data['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC',
            'orderByField' => '',
        ];

        // Check if ordering column is provided and valid
        if (
            isset($data['order'][0]['column']) &&
            $data['columns'][$data['order'][0]['column']]['orderable'] === 'true' &&
            isset($data['columns'][$data['order'][0]['column']]['data'])
        ) {
            $paginationData['orderByField'] = $data['columns'][$data['order'][0]['column']]['data'];
        }

        return $paginationData;
    }

    /**
     * Get paginated data with various pagination types.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  array  $postData  The request data.
     * @param  int  $type  The type of pagination (1: link, 2: load more,  3: load old 4: scroll).
     * @return array The paginated response.
     */
    public function getData(Builder $query, array $postData, int $type = 1): array
    {
        $response = [];
        $response['limit'] = min((int) ($postData['limit'] ?? 20), 100);

        // Calculate total records only for type 1 pagination
        if ($type === 1) {
            $response['total'] = $query->count();
        }

        // Apply sorting if provided
        if (! empty($postData['sort']['field'])) {
            $query->orderBy($postData['sort']['field'], $postData['sort']['direction'] ?? 'desc');
        }

        // Set offset or page-based pagination
        if (isset($postData['offset'])) {
            $response['offset'] = (int) ($postData['offset'] ?? 0);
            $response['page'] = ($response['offset'] / $response['limit']) + 1;
        } else {
            $response['page'] = (int) ($postData['page'] ?? 1);
            $response['offset'] = ($response['page'] - 1) * $response['limit'];
        }
        // Apply the offset
        $query->offset($response['offset']);
        // Apply the limit
        $query->limit($response['limit']);

        $response['data'] = $query->get();

        // Generate pagination links based on pagination type
        if ($type === 1) {
            $response['links'] = $this->getLinks($response['page'], $response['total'], $response['limit']);
        } elseif (! $response['data']->isEmpty()) {
            if (count($response['data']) < $response['limit']) {
                $response['links'] = '';
            } else {
                $response['links'] = $this->getLoadMore($response['page'], $type);
            }
        }

        $start = $response['offset'] + 1;
        $end = $response['offset'] + $response['limit'];
        if ($type == 1 && $end > $response['total']) {
            $end = $response['total'];
            $response['count'] = "Showing $start-$end of $response[total] items";
        }

        return $response;
    }

    /**
     * Generate pagination links for default pagination.
     *
     * @param  int  $page  The current page.
     * @param  int  $total  The total number of records.
     * @param  int  $limit  The number of records per page.
     * @return string HTML for pagination links.
     */
    public function getLinks(int $page = 1, int $total = 0, int $limit = 20)
    {
        if ($total === 0 || $total <= $limit) {
            return '';
        }

        $lastPage = (int) ceil($total / $limit);

        return view('common/pagination', ['page' => $page, 'lastPage' => $lastPage, 'total' => $total, 'limit' => $limit]);
    }

    /**
     * Generate a "Load More" button for pagination.
     *
     * @param  int  $page  The current page.
     * @param  int  $type  The type of pagination (2: loadmore , 3: loadold, 4: scroll).
     * @return string HTML for the "Load More" button.
     */
    public function getLoadMore(int $page, $type): string
    {
        return '<div class="pagination-load-more" '.$type == 4 ? 'style="display:none"' : ''.'><button class="btn btn-default page-link" data-page="'.($page + 1).'">Load More</button></div>';
    }
}
