<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::tokensExpireIn(
            now()->addMinutes((int) env('PASSPORT_TOKEN_EXPIRE_IN', 1440))
        );

        Passport::refreshTokensExpireIn(
            now()->addMinutes((int) env('PASSPORT_REFRESH_TOKEN_EXPIRE_IN', 43200))
        );

        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        Passport::tokensCan([
            'openid'         => 'OpenID Connect (basic identity)',
            'profile'        => 'Access your name, avatar, bio, gender and date of birth',
            'email'          => 'Access your email address',
            'phone'          => 'Access your phone number',
            'address'        => 'Access your address',
            'offline_access' => 'Maintain access when not active (refresh tokens)',
        ]);

        Passport::setDefaultScope(['openid', 'profile', 'email']);

        Passport::authorizationView('auth.oauth-authorize');
    }
}
