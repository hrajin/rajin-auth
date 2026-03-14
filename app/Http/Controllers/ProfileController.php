<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        return view('profile.edit', compact('user'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it was uploaded (not a Google URL)
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . basename($user->avatar))) {
                Storage::disk('public')->delete('avatars/' . basename($user->avatar));
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = Storage::url($path);
        }

        // Update users table fields
        $user->fill([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($user->isDirty('phone_number')) {
            $user->phone_verified_at = null;
        }

        $user->save();

        // Update user_profiles table
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'date_of_birth'  => $data['date_of_birth']  ?? null,
                'gender'         => $data['gender']         ?? null,
                'bio'            => $data['bio']            ?? null,
                'website'        => $data['website']        ?? null,
                'street_address' => $data['street_address'] ?? null,
                'city'           => $data['city']           ?? null,
                'state'          => $data['state']          ?? null,
                'postal_code'    => $data['postal_code']    ?? null,
                'country'        => $data['country']        ?? null,
            ]
        );

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('toast_success', 'Profile saved.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
