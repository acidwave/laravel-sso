<?php

namespace AcidWave\LaravelSSO\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use AcidWave\LaravelSSO\Traits\ApiResponser;
use Acidwave\LaravelSSO\Requests\CheckBrokerRequest;
use Illuminate\Routing\Controller as BaseController;

class ServerController extends BaseController
{
    use ApiResponser;

    /**
     * Check broker validity.
     *
     * @param  \App\Http\Requests\CheckBrokerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function broker(CheckBrokerRequest $request)
    {
        $brokerInfo = $request->validated();
        $broker = config('laravel-sso.brokersModel')::firstWhere('name', $brokerInfo['broker']);
        if (!$broker) 
            return $this->errorResponse('Wrong broker', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        if (Hash::check($brokerInfo['command'] . $brokerInfo['token'] . $broker->secret, $brokerInfo['hash'])) {
            return $this->successResponse([
                'status' => 'ok'
            ]);
        }
        return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
    }

    public function check(CheckBrokerRequest $request)
    {
        $brokerInfo = $request->validated();
        $broker = config('laravel-sso.brokersModel')::firstWhere('name', $brokerInfo['broker']);
        if (!$broker) 
            return $this->errorResponse('Wrong broker', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        if (Hash::check($brokerInfo['command'] . $brokerInfo['token'] . $broker->secret, $brokerInfo['hash'])) {
            $status = !is_null($request->user()) ? 'authorized' : 'unauthorized';
            $token = $request->bearerToken();
            $hash = Hash::make($status . $brokerInfo['token'] . $broker->secret);
            return $this->successResponse([
                'status' => $status,
                'authorization' => $token,
                'hash' => $hash,
            ]);
        }
        return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
    }

    public function me(CheckBrokerRequest $request)
    {
        $brokerInfo = $request->validated();
        $broker = config('laravel-sso.brokersModel')::firstWhere('name', $brokerInfo['broker']);
        if (!$broker) 
            return $this->errorResponse('Wrong broker', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        if (Hash::check($brokerInfo['command'] . $brokerInfo['token'] . $broker->secret, $brokerInfo['hash'])) {
            $user = $request->user();
            if ($user) {
                $config = $this->getConfig('laravel-sso');
                $status = 'ok';
                $hash = Hash::make($status . $brokerInfo['token'] . $broker->secret);
                return $this->successResponse([
                    'user' => new $config['userResource']($user),
                    'status' => $status,
                    'hash' => $hash,
                ], Response::HTTP_OK, ['Authorization' => $request->bearerToken()]);
            } else {
                $status = "User not found";
                $hash = Hash::make($status . $brokerInfo['token'] . $broker->secret);
                return $this->errorResponse([
                    'status' => $status,
                    'hash' => $hash,
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
    }

    public function refresh(CheckBrokerRequest $request)
    {
        $brokerInfo = $request->validated();
        $broker = config('laravel-sso.brokersModel')::firstWhere('name', $brokerInfo['broker']);
        if (!$broker) 
            return $this->errorResponse('Wrong broker', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        if (Hash::check($brokerInfo['command'] . $brokerInfo['token'] . $broker->secret, $brokerInfo['hash'])) {
            $user = $request->user();
            if ($user) {
                $status = 'ok';
                $hash = Hash::make($status . $brokerInfo['token'] . $broker->secret);
                Cookie::queue(Cookie::make('username', $user->name, 0, '/', config('laravel-sso.domain')));
                Cookie::queue(Cookie::make('authorization', $request->bearerToken(), 0, '/', config('laravel-sso.domain'), true, false));
                return $this->successResponse([
                    'status' => $status,
                    'hash' => $hash,
                ], Response::HTTP_OK, ['Authorization' => $request->bearerToken()]);
            } else {
                $error = "Something went wrong, try again.";
                return $this->errorResponse($error, Response::HTTP_UNAUTHORIZED);
            }
        }
        return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
    }

    public function logout(CheckBrokerRequest $request)
    {
        $brokerInfo = $request->validated();
        $broker = config('laravel-sso.brokersModel')::firstWhere('name', $brokerInfo['broker']);
        if (!$broker) 
            return $this->errorResponse('Wrong broker', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        if (Hash::check($brokerInfo['command'] . $brokerInfo['token'] . $broker->secret, $brokerInfo['hash'])) {
            $request->user()->currentAccessToken()->delete();
            $status = 'unauthorized';
            $hash = Hash::make($status . $brokerInfo['token'] . $broker->secret);
            return $this->successResponse([
                'status' => $status,
                'hash' => $hash,
            ]);
        }
        return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
    }
}
