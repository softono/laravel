<?php

namespace Tests\Feature;

use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Contact\Requests\ContactRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    private function errors(string $request, array $input): array
    {
        return Validator::make($input, (new $request)->rules())->errors()->keys();
    }

    private function registration(array $override = []): array
    {
        return $override + [
            'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@example.com', 'phone' => '9876543210',
            'password' => 'secret1', 'confirm_password' => 'secret1', 'agree' => '1',
        ];
    }

    public function test_a_complete_registration_passes(): void
    {
        $this->assertSame([], $this->errors(RegisterRequest::class, $this->registration()));
    }

    public function test_registration_rejects_bad_fields(): void
    {
        $this->assertContains('first_name', $this->errors(RegisterRequest::class, $this->registration(['first_name' => 'J4'])));
        $this->assertContains('phone', $this->errors(RegisterRequest::class, $this->registration(['phone' => '123'])));
        $this->assertContains('confirm_password', $this->errors(RegisterRequest::class, $this->registration(['confirm_password' => 'other'])));
        $this->assertContains('agree', $this->errors(RegisterRequest::class, $this->registration(['agree' => '0'])));
        $this->assertContains('email', $this->errors(RegisterRequest::class, $this->registration(['email' => 'nope'])));
    }

    public function test_login_needs_an_email_and_password(): void
    {
        $this->assertSame(['email', 'password'], $this->errors(LoginRequest::class, []));
        $this->assertSame([], $this->errors(LoginRequest::class, ['email' => 'a@b.co', 'password' => 'x', 'remember' => true]));
    }

    public function test_contact_message_is_capped(): void
    {
        $ok = ['name' => 'A', 'email' => 'a@b.co', 'subject' => 'Hi', 'message' => 'Hello'];

        $this->assertSame([], $this->errors(ContactRequest::class, $ok));
        $this->assertContains('message', $this->errors(ContactRequest::class, ['message' => str_repeat('x', 1001)] + $ok));
    }
}
