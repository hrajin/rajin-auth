<?php

use App\Http\Controllers\Api\ChangePasswordController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokenIntrospectController;
use App\Http\Controllers\Api\UserInfoController;
use Illuminate\Support\Facades\Route;

// Health check — public
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});

// Token introspection — authenticated by client credentials (no user token needed)
Route::post('/token/introspect', TokenIntrospectController::class);

Route::middleware('auth:api')->group(function () {
    Route::get('/userinfo', UserInfoController::class);
    Route::get('/user', UserInfoController::class);

    // Logout
    Route::post('/logout', [LogoutController::class, 'session']);
    Route::post('/logout/all', [LogoutController::class, 'global']);

    // Password management
    Route::post('/password/change', ChangePasswordController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
});
