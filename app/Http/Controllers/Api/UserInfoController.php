<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInfoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user   = $request->user();
        $scopes = $user->token()->scopes;

        $claims = ['sub' => (string) $user->id];

        if (in_array('profile', $scopes)) {
            $claims['name']    = $user->name;
            $claims['picture'] = $user->avatar;
        }

        if (in_array('email', $scopes)) {
            $claims['email']          = $user->email;
            $claims['email_verified'] = ! is_null($user->email_verified_at);
        }

        return response()->json($claims);
    }
}
