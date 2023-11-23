<?php

use Illuminate\Support\Facades\Route;
use AcidWave\LaravelSSO\Controllers\BrokerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(['prefix' => 'auth/v1'], function () {
    Route::post('login', [BrokerController::class, 'login'])->name('auth.login');
    Route::get('refresh', [BrokerController::class, 'refresh'])->name('auth.refresh');
    Route::get('me', [BrokerController::class, 'me'])->name('auth.me');
    Route::post('logout', [BrokerController::class, 'logout'])->name('auth.logout');
});

Route::get('/login', [BrokerController::class, 'login'])->name('login');
Route::get('/logout', [BrokerController::class, 'logout'])->name('logout');
Route::get('/authCallback', [BrokerController::class, 'authCallback'])->name('auth-callback');
