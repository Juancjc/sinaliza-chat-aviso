<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiMailService
{
    public function send(string $to, string $subject, string $body): void
    {
        Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'x-api-key' => config('services.mail_api.key'),
                'x-api-token' => config('services.mail_api.token'),
            ])
            ->timeout((int) config('services.mail_api.timeout'))
            ->post(config('services.mail_api.url'), compact('to', 'subject', 'body'))
            ->throw();
    }
}
