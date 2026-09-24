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
            'limit' => min((int) ($data['length'] ?? 20), 100),
            'offset' => (int) ($data['start'] ?? 0),
            'orderBy' => isset($data['order'][0]['dir']) && $data['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC',
            'orderByField' => '',
        ];

        // Sort only by a plain column name that the table declared orderable.
        $column = $data['columns'][$data['order'][0]['column'] ?? -1] ?? null;
        if ($column && ($column['orderable'] ?? 'false') === 'true' && preg_match('/^\w+$/', $column['data'] ?? '')) {
            $paginationData['orderByField'] = $column['data'];
        }

        return $paginationData;
    }
}
