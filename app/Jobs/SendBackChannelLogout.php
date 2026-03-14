<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;

class SendBackChannelLogout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly string $clientId,
        public readonly int    $userId,
    ) {}

    public function handle(): void
    {
        $client = Client::find($this->clientId);

        if (!$client || !$client->logout_uri || !$client->logout_secret) {
            return;
        }

        $payload = [
            'sub' => (string) $this->userId,
            'iat' => now()->timestamp,
        ];

        // HMAC-SHA256 signature so the client app can verify this came from us
        $signature = hash_hmac('sha256', json_encode($payload), $client->logout_secret);

        Http::timeout(5)
            ->withHeaders(['X-Signature' => $signature])
            ->post($client->logout_uri, $payload);
    }
}
