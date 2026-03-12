<?php

namespace App\Providers;

use App\Listeners\RecordDeviceSession;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('auth', fn (Request $req) =>
            Limit::perMinute(10)->by($req->ip())
        );

        RateLimiter::for('oauth-token', fn (Request $req) =>
            Limit::perMinute(30)->by($req->ip())
        );

        Event::listen(AccessTokenCreated::class, RecordDeviceSession::class);
    }
}
