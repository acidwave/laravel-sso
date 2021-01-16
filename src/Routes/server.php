<?php

/**
 * Routes which is neccessary for the SSO server.
 */

Route::middleware('api')->prefix('api/sso')->group(function () {
    Route::post('login', 'Acidwave\LaravelSSO\Controllers\ServerController@login');
    Route::post('logout', 'Acidwave\LaravelSSO\Controllers\ServerController@logout');
    Route::get('attach', 'Acidwave\LaravelSSO\Controllers\ServerController@attach');
    Route::get('userInfo', 'Acidwave\LaravelSSO\Controllers\ServerController@userInfo');
});
