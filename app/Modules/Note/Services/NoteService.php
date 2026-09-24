<?php

namespace App\Modules\Note\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\NoteRepository;

class NoteService
{
    public function __construct(
        protected NoteRepository $notes,
        protected General $general,
    ) {}

    /** DataTables payload of the user's notes, with an action cell per row. */
    public function datatable(User $user, array $post): array
    {
        $result = $this->notes->datatable($user->id, $post);

        $result['data'] = $result['data']->map(function ($row) {
            $row->updated_at = $this->general->dateFormat($row->updated_at);
            $row->action = view('modules.note.partials.row-actions', ['row' => $row])->render();

            return $row;
        })->all();

        return ApiResult::success('', $result);
    }

    /**
     * Creates the note, or updates it when an id is given.
     *
     * @param  array{id?: ?int, title: string, note?: ?string}  $data
     */
    public function save(User $user, array $data): array
    {
        $fields = ['title' => $data['title'], 'note' => $data['note'] ?? null];

        if (empty($data['id'])) {
            $this->notes->create($fields + ['user_id' => $user->id]);

            return ApiResult::success('Note created successfully');
        }

        $note = $this->notes->findForUser($user->id, $data['id']);

        if (! $note) {
            return ApiResult::failure('Note not found');
        }

        $this->notes->update($note, $fields);

        return ApiResult::success('Note updated successfully');
    }

    public function delete(User $user, int|string $id): array
    {
        $note = $this->notes->findForUser($user->id, $id);

        if (! $note) {
            return ApiResult::failure('Note not found');
        }

        $this->notes->delete($note);

        return ApiResult::success('Note deleted successfully');
    }
}
