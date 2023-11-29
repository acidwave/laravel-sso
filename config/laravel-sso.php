<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel SSO Settings
     |--------------------------------------------------------------------------
     |
     | Set type of this web page. Possible options are: 'server' and 'broker'.
     |
     | You must specify either 'server' or 'broker'.
     |
     */

    'type' => env('SSO_TYPE', 'server'),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO server.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO server.
     |
     */

    'usersModel' => \App\Models\User::class,
    'brokersModel' => AcidWave\LaravelSSO\Models\Broker::class,
    'userResource' => \AcidWave\LaravelSSO\Resources\UserResource::class,

    // Table used in Acidwave\LaravelSSO\Models\Broker model
    'brokersTable' => 'brokers',

    // What is the name of the column that users use to login with (generally 'username' or 'email)
    'usernameField' => env('SSO_USERNAME_FIELD', 'username'),

    // Logged in user fields sent to brokers.
    'userFields' => [
        // Return array field name => database column name
        'id' => 'id',
    ],

    // Domain for exchange data between server and brokers
    'domain' => env('SSO_SESSION_DOMAIN', 'SESSION_DOMAIN'),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO broker.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO broker.
     |
     */

    'serverUrl' => env('SSO_SERVER_URL', null),
    'brokerName' => env('SSO_BROKER_NAME', null),
    'brokerSecret' => env('SSO_BROKER_SECRET', null),
];
