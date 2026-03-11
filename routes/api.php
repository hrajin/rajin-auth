<?php

use App\Http\Controllers\Api\UserInfoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/userinfo', UserInfoController::class);
    Route::get('/user', UserInfoController::class);
});
