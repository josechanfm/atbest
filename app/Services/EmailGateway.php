<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmailGateway
{
    public function send(string $to, string $subject, string $content): bool
    {
        $baseUrl = rtrim((string) config('services.genie_email.base_url'), '/');
        $abbr = config('services.genie_email.abbr');
        $from = config('services.genie_email.from');
        if ($baseUrl === '' || blank($abbr) || blank($from)) {
            throw new RuntimeException('Invitation email gateway configuration is incomplete.');
        }
        $response = Http::timeout(10)
            ->withoutVerifying() // <-- Use this instead
            ->acceptJson()
            ->get($baseUrl , [
                'abbr' => $abbr,
                'email_from' => $from,
                'email_to' => $to,
                'subject' => $subject,
                'content' => $content,
            ])
            ->throw();
        if ($response->failed()) {
            Log::error('Email send failed', [
                'url'      => $baseUrl,
                'to'       => $to,
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        }
        return true;            
       
    }
}
