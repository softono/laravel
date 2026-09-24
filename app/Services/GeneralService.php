<?php

namespace App\Services;

use App\Helpers\General;
use App\Repositories\ContactMessageRepository;
use Illuminate\Support\Facades\Validator;

class GeneralService
{
    public function __construct(
        protected ContactMessageRepository $contactMessages,
    ) {}

    public function contactProcess($postData)
    {

        // Check reCAPTCHA validation
        $general = new General;
        if ($general->rateLimit('contact')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }

        // if ($general->recaptchaFails()) {
        //     return ['status' => 0, 'message' => 'Please check reCAPTCHA.'];
        // }

        $validator = Validator::make($postData, [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'regex:/^[\w\.\-]+@[a-zA-Z\d\-]+\.[a-zA-Z]{2,}$/', // Ensure email contains a dot
            ],
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ], [
            'email.regex' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        /* SAVE CONTACT MESSAGE TO DATABASE */

        $this->contactMessages->create([
            'user_id' => auth()->id(),
            'email' => $postData['email'],
            'subject' => $postData['subject'],
            'message' => $postData['message'],
        ]);

        /* SEND EMAIL TO ADMIN */
        $general->sendEmail(config('setting.admin_email'), 'admin_contact', $postData);

        return ['status' => 1, 'message' => 'Submit request successfully', 'next' => 'refresh'];
    }
}
