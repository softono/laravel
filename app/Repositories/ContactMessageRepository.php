<?php

namespace App\Repositories;

use App\Models\ContactMessages;
use Illuminate\Support\Collection;

class ContactMessageRepository
{
    public function create(array $data): ContactMessages
    {
        return ContactMessages::create($data);
    }

    public function duplicateExists(string $email, string $subject, string $message): bool
    {
        return ContactMessages::where('to_user', $email)->where('subject', $subject)->where('message', $message)->exists();
    }

    /** @return Collection<int, ContactMessages> */
    public function forUser(string $userId): Collection
    {
        return ContactMessages::where('user_id', $userId)->orderByDesc('created_at')->get();
    }
}
