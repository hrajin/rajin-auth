<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInfoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user   = $request->user()->load('profile');
        $scopes = $user->token()->scopes;

        $claims = ['sub' => (string) $user->id];

        if (in_array('profile', $scopes)) {
            $claims['name']       = $user->name;
            $claims['picture']    = $user->avatar;
            $claims['gender']     = $user->profile?->gender;
            $claims['birthdate']  = $user->profile?->date_of_birth?->format('Y-m-d');
            $claims['website']    = $user->profile?->website;
            $claims['bio']        = $user->profile?->bio;
            $claims['updated_at'] = $user->updated_at?->timestamp;
        }

        if (in_array('email', $scopes)) {
            $claims['email']          = $user->email;
            $claims['email_verified'] = ! is_null($user->email_verified_at);
        }

        if (in_array('phone', $scopes)) {
            $claims['phone_number']          = $user->phone_number;
            $claims['phone_number_verified'] = ! is_null($user->phone_verified_at);
        }

        if (in_array('address', $scopes)) {
            $profile = $user->profile;
            $claims['address'] = [
                'street_address' => $profile?->street_address,
                'locality'       => $profile?->city,
                'region'         => $profile?->state,
                'postal_code'    => $profile?->postal_code,
                'country'        => $profile?->country,
            ];
        }

        return response()->json($claims);
    }
}
