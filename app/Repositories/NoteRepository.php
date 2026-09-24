<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\Note;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Every method is scoped to one user: a note is never reachable by id alone. */
class NoteRepository
{
    public function __construct(protected Pagination $pagination) {}

    public function findForUser(string $userId, int|string $id): ?Note
    {
        return Note::where('user_id', $userId)->where('id', $id)->first();
    }

    public function create(array $data): Note
    {
        return Note::create($data);
    }

    public function update(Note $note, array $data): bool
    {
        return $note->update($data);
    }

    public function delete(Note $note): ?bool
    {
        return $note->delete();
    }

    /**
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(string $userId, array $post): array
    {
        $query = DB::table('notes')->select('id', 'title', 'note', 'updated_at')->where('user_id', $userId);

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('note', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }
}
