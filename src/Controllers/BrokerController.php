<?php

namespace AcidWave\LaravelSSO\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use AcidWave\LaravelSSO\LaravelSSOBroker;
use Illuminate\Support\Facades\Cookie;
use AcidWave\LaravelSSO\Traits\ApiResponser;
use AcidWave\LaravelSSO\Requests\CheckAuthRequest;
use Illuminate\Routing\Controller as BaseController;

class BrokerController extends BaseController
{
    use ApiResponser;

    /**
     * Request logged in user information
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $ssoHelper = new LaravelSSOBroker($request->bearerToken());
        $ssoResponse = $ssoHelper->makeRequest('api/sso/v1/me');

        if ($ssoResponse->ok()) {
            $response = $ssoResponse->json('data');
            if (!$ssoHelper->checkResponse([$response['status']], $response['hash']))
                return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
            $user = $response['user'];
            Cookie::queue(Cookie::make('username', $user['name'], 0, '/', '.dev.acidwave.ru'));
            Cookie::queue(Cookie::make('authorization', $ssoResponse->header('Authorization'), 0, '/', '.dev.acidwave.ru', true, false));
            return $this->successResponse($user, Response::HTTP_OK, ['Authorization' => $ssoResponse->header('Authorization')]);
        } else {
            $error = "User not found";
            Cookie::expire('authorization', '/', '.dev.acidwave.ru');
            Cookie::expire('username', '/', '.dev.acidwave.ru');
            return $this->errorResponse($error, Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Request logged in user token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $ssoHelper = new LaravelSSOBroker($request->bearerToken());
        $response = $ssoHelper->makeRequest('api/sso/v1/refresh');

        if ($response->ok()) {
            if (!$ssoHelper->checkResponse([$response->json('data')['status']], $response->json('data')['hash']))
                return $this->errorResponse('Wrong hash', Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
            return $this->successResponse($response->json('data'), Response::HTTP_OK, ['Authorization' => $response->header('Authorization')]);
        } else {
            $error = "User not found";
            return $this->errorResponse($error, Response::HTTP_UNAUTHORIZED);
        }
    }

    function login(Request $request): void
    {
        $ssoHelper = new LaravelSSOBroker($request->bearerToken() ?? '');
        $ssoHelper->redirectRequest('login', $request->header('referer', '/'));
    }

    function logout(Request $request): void
    {
        $ssoHelper = new LaravelSSOBroker($request->bearerToken() ?? '');
        $ssoHelper->redirectRequest('logout', $request->header('referer', '/'));
    }

    function authCallback(CheckAuthRequest $request): JsonResponse | RedirectResponse
    {
        $authInfo = $request->validated();
        $authInfo['authorization'] = $authInfo['authorization'] ?? '';
        $ssoHelper = new LaravelSSOBroker($authInfo['authorization']);
        $return_url = $ssoHelper->getReturnUrl();
        $verified = $ssoHelper->checkResponse([$authInfo['status'], $authInfo['authorization']], $authInfo['hash']);
        $ssoHelper->deleteToken();
        if (!$verified) {
            return $request->expectsJson()
                ? $this->errorResponse(['return_url' => $return_url], Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS)
                : redirect($return_url);
        } elseif ($authInfo['status'] == 'authorized') {
            return $request->expectsJson()
                ? $this->successResponse(
                    ['return_url' => $return_url],
                    Response::HTTP_OK,
                    ['Authorization' => $authInfo['authorization']]
                )
                : redirect($return_url)->withHeaders(['Authorization' => $authInfo['authorization']]);
        } else {
            return $request->expectsJson()
                ? $this->errorResponse(['return_url' => $return_url], Response::HTTP_UNAUTHORIZED)
                : redirect($return_url);
        }
    }
}
