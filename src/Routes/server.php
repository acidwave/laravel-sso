<?php

use Illuminate\Support\Facades\Route;
use Acidwave\LaravelSSO\Controllers\ServerController;

/**
 * Routes which is neccessary for the SSO server.
 */

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/sso/v1',
], function () {
    Route::post('broker', [ServerController::class, 'broker']);
    Route::post('check', [ServerController::class, 'check']);
    Route::post('logout', [ServerController::class, 'logout']);
    Route::post('me', [ServerController::class, 'me']);
    Route::post('refresh', [ServerController::class, 'refresh']);
});
