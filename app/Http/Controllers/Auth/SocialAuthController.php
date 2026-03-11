<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        $socialUser = Socialite::driver($provider)->user();

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            $socialAccount->update([
                'access_token'     => $socialUser->token,
                'refresh_token'    => $socialUser->refreshToken,
                'token_expires_at' => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]);

            Auth::login($socialAccount->user);

            return $this->redirectAfterLogin();
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name'  => $socialUser->getName(), 'avatar' => $socialUser->getAvatar()]
        );

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->socialAccounts()->create([
            'provider'         => $provider,
            'provider_id'      => $socialUser->getId(),
            'access_token'     => $socialUser->token,
            'refresh_token'    => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);

        Auth::login($user);

        return $this->redirectAfterLogin();
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        // If there's a pending OAuth authorization request, resume it.
        if (session()->has('url.intended')) {
            return redirect()->intended();
        }

        return redirect()->intended(route('dashboard'));
    }
}
