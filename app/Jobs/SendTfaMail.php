<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTfaMail extends Mailable
{
    use Queueable, SerializesModels;

    // public $template;
    public $data;

    public $to;

    public function __construct($data, $to)
    {
        $this->data = $data;
        $this->to = $to;
    }

    public function build()
    {
        return $this->subject($this->data['subject'] ?? 'Your 2FA Code')
            ->view('email.tfa')
            ->with([
                'first_name' => $this->data['first_name'] ?? '',
                'last_name' => $this->data['last_name'] ?? '',
                'otp' => $this->data['otp'] ?? '',
                'subject' => $this->data['subject'] ?? 'Your 2FA Code',
            ]);
    }
}
