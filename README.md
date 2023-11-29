# Simple PHP SSO integration for Laravel

<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

### Requirements
* Laravel 9+
* PHP 8.0+

### Words meanings
* ***SSO*** - Single Sign-On.
* ***Server*** - page which works as SSO server, handles authentications, stores all sessions data.
* ***Broker*** - your page which is used visited by clients/users.
* ***Client/User*** - your every visitor.

### How it works?
Client visits Broker and unique token is generated. When new token is generated we need to attach Client session to his session in Broker so he will be redirected to Server and back to Broker at this moment new session in Server will be created and associated with Client session in Broker's page. When Client visits other Broker same steps will be done except that when Client will be redirected to Server he already use his old session and same session id which associated with Broker#1.

# Installation
### Server
Install this package using composer.
```shell
$ composer require acidwave/laravel-sso
```


Copy config file to Laravel project `config/` folder.
```shell
$ php artisan vendor:publish --provider="AcidWave\LaravelSSO\SSOServiceProvider"
```


Create table where all brokers will be saved.
```shell
$ php artisan migrate --path=vendor/acidwave/laravel-sso/database/migrations
```


Edit your `app/Http/Kernel.php` by adding queued cookies middleware to `api` middlewares array.
This is necessary because we need cookies to work in API routes.
```php
'api' => [
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```
Set new option in your `.env` file:
```shell
SSO_SESSION_DOMAIN=
```

Now you should create brokers.
You can create new broker using following Artisan CLI command:
```shell
$ php artisan sso:broker:create {name}
```

----------

### Broker
Install this package using composer.
```shell
$ composer require acidwave/laravel-sso
```


Copy config file to Laravel project `config/` folder.
```shell
$ php artisan vendor:publish --provider="AcidWave\LaravelSSO\SSOServiceProvider"
```


Change `type` value in `config/laravel-sso.php` file from `server`
 to `broker`.

 

Set 4 new options in your `.env` file:
```shell
SSO_SERVER_URL=
SSO_BROKER_NAME=
SSO_BROKER_SECRET=
SSO_SESSION_DOMAIN=
SSO_TYPE=broker
```
`SSO_SERVER_URL` is your server's http url without trailing slash. `SSO_BROKER_NAME` and `SSO_BROKER_SECRET` must be data which exists in your server's `brokers` table.

Optionally set the column of your authentication username field on your server ('username' by default) `.env` file:
```shell
SSO_USERNAME_FIELD=email
```

Edit your `app/Http/Kernel.php` by adding `\AcidWave\LaravelSSO\Middleware\SsoAuthCheck::class` middleware to middleware. It should look like this:
```php
protected $middleware = [
        ...
        \AcidWave\LaravelSSO\Middleware\SsoAuthCheck::class,
    ];
```


That's all. For other Broker pages you should repeat everything from the beginning just changing your Broker name and secret in configuration file.




Example `.env` options:
```shell
SSO_SERVER_URL=https://server.test
SSO_BROKER_NAME=site1
SSO_BROKER_SECRET=892asjdajsdksja74jh38kljk2929023
SSO_SESSION_DOMAIN=".server.test"
SSO_USERNAME_FIELD=email
SSO_TYPE=broker
```
