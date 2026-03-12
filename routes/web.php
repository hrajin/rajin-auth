<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;

// Apply device limit enforcement + rate limiting to Passport's token endpoint
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
    ->middleware(['throttle:oauth-token', \App\Http\Middleware\EnforceDeviceLimit::class])
    ->name('passport.token');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Social Auth
Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider'])
    ->name('auth.social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
    ->name('auth.social.callback');

// Admin
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('clients', Admin\ClientController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('users', Admin\UserController::class)->only(['index']);
});

require __DIR__.'/auth.php';
