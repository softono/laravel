<?php

namespace App\Modules\Admin\User\Services;

use App\Constants\UserRole;
use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Repositories\Auth\UserRepository;
use App\Repositories\ContactMessageRepository;

/** Emails an admin sends to a user; each one is kept in contact_messages and shown on the user's page. */
class UserMailService
{
    public function __construct(
        protected ContactMessageRepository $messages,
        protected UserRepository $users,
        protected General $general,
    ) {}

    public function send(string $userId, string $subject, string $message): array
    {
        $recipient = $this->users->findByIdAndRole($userId, UserRole::USER);

        if (! $recipient) {
            return ApiResult::failure('No data found');
        }

        $this->messages->create([
            'user_id' => $recipient->id,
            'to_user' => $recipient->email,
            'subject' => $subject,
            'message' => $message,
        ]);

        $this->general->sendEmail($recipient->email, 'send_mail', ['subject' => $subject, 'message' => $message]);

        return ApiResult::success('Email sent successfully');
    }
}
