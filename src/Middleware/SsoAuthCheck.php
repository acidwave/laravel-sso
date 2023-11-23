<?php

namespace AcidWave\LaravelSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use AcidWave\LaravelSSO\Controllers\SsoController;

class SsoAuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $cookie_token = $request->cookie('authorization', false);
        $header_token = $request->bearerToken();
        $nextRequest = $next($request);
        if ($header_token) {
            $sso = new SsoController($header_token);
            $response = $sso->makeRequest('api/sso/v1/check');
            if ($response->getStatusCode() !== 200) {
                Cookie::expire('authorization', '/', '.dev.acidwave.ru');
                Cookie::expire('username', '/', '.dev.acidwave.ru');
                $nextRequest->header('Authorization', '');
            }
        } elseif ($cookie_token) {
            $sso = new SsoController($cookie_token);
            $response = $sso->makeRequest('api/sso/v1/check');
            if ($response->getStatusCode() !== 200) {
                Cookie::expire('authorization', '/', '.dev.acidwave.ru');
                Cookie::expire('username', '/', '.dev.acidwave.ru');
                $nextRequest->header('Authorization', '');
            } else {
                $nextRequest->header('Authorization', 'Bearer ' . $cookie_token);
            }
        }
        return $nextRequest;
    }
}
