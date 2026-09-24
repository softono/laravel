<?php

namespace App\Modules\Admin\User\Services;

use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\ContactMessageRepository;

/** Emails an admin sends to a user; each one is kept in contact_messages and shown on the user's page. */
class UserMailService
{
    public function __construct(
        protected ContactMessageRepository $messages,
        protected General $general,
    ) {}

    public function send(User $recipient, string $subject, string $message): void
    {
        $this->messages->create([
            'user_id' => $recipient->id,
            'to_user' => $recipient->email,
            'subject' => $subject,
            'message' => $message,
        ]);

        $this->general->sendEmail($recipient->email, 'send_mail', ['subject' => $subject, 'message' => $message]);
    }
}
