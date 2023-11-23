<?php

namespace AcidWave\LaravelSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use AcidWave\LaravelSSO\LaravelSSOBroker;

class SsoAuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * 
     * @return \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)
     */
    public function handle(Request $request, Closure $next)
    {
        $cookie_token = $request->cookie('authorization', false);
        $header_token = $request->bearerToken();
        $nextRequest = $next($request);
        if ($header_token) {
            $ssoBroker = new LaravelSSOBroker($header_token);
        } elseif ($cookie_token) {
            $ssoBroker = new LaravelSSOBroker($cookie_token);
        } else {
            $ssoBroker = new LaravelSSOBroker();
        }
        $response = $ssoBroker->makeRequest('api/sso/v1/check');

        if ($response->getStatusCode() !== 200 || !$ssoBroker->checkResponse([$response->json('data')['status']], $response->json('data')['hash'])) {
            Cookie::expire('authorization', '/', '.dev.acidwave.ru');
            Cookie::expire('username', '/', '.dev.acidwave.ru');
            $nextRequest->header('Authorization', '');
        } else {
            $nextRequest->header('Authorization', 'Bearer ' . $cookie_token);
        }
        $ssoBroker->deleteToken();
        return $nextRequest;
    }
}
