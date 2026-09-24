<?php

namespace App\Services;

use App\Repositories\EmailTemplateRepository;

/** Renders an email template with its {{placeholders}} filled in and wrapped in the shared layout. */
class EmailTemplateService
{
    public function __construct(protected EmailTemplateRepository $templates) {}

    /**
     * @param  array<string, scalar>  $data
     * @return array{subject: string, body: string}
     */
    public function render(string $key, array $data = []): array
    {
        $template = $this->templates->findByKey($key);

        if (! $template) {
            return ['subject' => '', 'body' => ''];
        }

        $data['app_name'] = config('setting.app_name');

        $placeholders = [];
        foreach ($data as $name => $value) {
            $placeholders['{{'.$name.'}}'] = (string) $value;
        }

        $subject = strtr($template->subject, $placeholders);
        $body = strtr($template->body, $placeholders);

        return ['subject' => $subject, 'body' => view('email.template', ['subject' => $subject, 'body' => $body])->render()];
    }

    /** The template as stored, without placeholder values, for previews. */
    public function preview(string $key): array
    {
        return $this->render($key);
    }
}
