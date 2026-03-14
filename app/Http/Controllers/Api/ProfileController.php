<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile;

        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'email_verified' => ! is_null($user->email_verified_at),
            'phone_number'   => $user->phone_number,
            'phone_verified' => ! is_null($user->phone_verified_at),
            'avatar'         => $user->avatar,
            'profile' => [
                'date_of_birth'  => $profile?->date_of_birth?->format('Y-m-d'),
                'gender'         => $profile?->gender,
                'bio'            => $profile?->bio,
                'website'        => $profile?->website,
                'street_address' => $profile?->street_address,
                'city'           => $profile?->city,
                'state'          => $profile?->state,
                'postal_code'    => $profile?->postal_code,
                'country'        => $profile?->country,
            ],
            'updated_at' => $user->updated_at->toISOString(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'phone_number'   => [
                'sometimes', 'nullable', 'string', 'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'date_of_birth'  => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender'         => ['sometimes', 'nullable', 'in:male,female,non_binary,prefer_not_to_say'],
            'bio'            => ['sometimes', 'nullable', 'string', 'max:500'],
            'website'        => ['sometimes', 'nullable', 'url', 'max:255'],
            'street_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'           => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code'    => ['sometimes', 'nullable', 'string', 'max:20'],
            'country'        => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        // Update users table
        $userFields = array_intersect_key($data, array_flip(['name', 'phone_number']));

        if (!empty($userFields)) {
            if (array_key_exists('phone_number', $userFields) && $user->phone_number !== $userFields['phone_number']) {
                $user->phone_verified_at = null;
            }

            $user->fill($userFields)->save();
        }

        // Update user_profiles table
        $profileFields = array_diff_key($data, array_flip(['name', 'phone_number']));

        if (!empty($profileFields)) {
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        return $this->show($request);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old avatar if it was a local upload (not a Google URL)
        if ($user->avatar) {
            $oldPath = 'avatars/' . basename($user->avatar);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => Storage::url($path)]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'avatar'  => $user->avatar,
        ]);
    }
}
