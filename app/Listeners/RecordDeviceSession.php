<?php

namespace App\Listeners;

use App\Models\DeviceSession;
use Illuminate\Http\Request;
use Laravel\Passport\Events\AccessTokenCreated;

class RecordDeviceSession
{
    public function __construct(private Request $request) {}

    public function handle(AccessTokenCreated $event): void
    {
        $fingerprint = hash('sha256', $this->request->userAgent() ?? '');

        // updateOrCreate so refreshing a token updates the existing device row
        // rather than creating a duplicate
        DeviceSession::updateOrCreate(
            [
                'user_id'            => $event->userId,
                'client_id'          => $event->clientId,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'token_id'       => $event->tokenId,
                'user_agent'     => $this->request->userAgent(),
                'ip_address'     => $this->request->ip(),
                'last_active_at' => now(),
            ]
        );
    }
}
