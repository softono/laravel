<?php

namespace App\Modules\Contact\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\ContactMessageRepository;

class ContactService
{
    public function __construct(
        protected ContactMessageRepository $messages,
        protected General $general,
    ) {}

    /**
     * Stores the message and notifies the admin.
     *
     * @param  array{name: string, email: string, subject: string, message: string}  $data
     */
    public function submit(array $data, ?User $user): array
    {
        if ($this->general->recaptchaFails()) {
            return ApiResult::failure('reCAPTCHA verification failed');
        }

        if ($this->messages->duplicateExists($data['email'], $data['subject'], $data['message'])) {
            return ApiResult::failure('You have already submitted this message');
        }

        $this->messages->create([
            'user_id' => $user?->id,
            'to_user' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        $this->general->sendEmail((string) config('setting.admin_email'), 'admin_contact', $data);

        return ApiResult::success('Thank you for contacting us. We will get back to you soon.');
    }
}
