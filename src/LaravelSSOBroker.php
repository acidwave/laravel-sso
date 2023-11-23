<?php

namespace AcidWave\LaravelSSO;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use AcidWave\LaravelSSO\Traits\ApiResponser;
use AcidWave\LaravelSSO\Exceptions\MissingConfigurationException;

class LaravelSSOBroker 
{
    use ApiResponser;
    /**
     * SSO server url.
     *
     * @var string
     */
    protected $ssoServerUrl;

    /**
     * Broker name.
     *
     * @var string
     */
    protected $brokerName;

    /**
     * Broker secret token.
     *
     * @var string
     */
    protected $brokerSecret;

    /**
     * User info retrieved from the SSO server.
     *
     * @var array
     */
    protected $userInfo;

    /**
     * Random token generated for the client and broker.
     *
     * @var string|null
     */
    protected $token;

    /**
     * Auth token generated for the broker on server.
     *
     * @var string|null
     */
    protected $authToken;

    protected $usernameField;

    public function __construct(string $token = '')
    {
        $this->ssoServerUrl = config('laravel-sso.serverUrl', null);
        $this->brokerName = config('laravel-sso.brokerName', null);
        $this->brokerSecret = config('laravel-sso.brokerSecret', null);
        $this->usernameField = config('laravel-sso.usernameField', null);
        $this->authToken = $token;

        if (!$this->ssoServerUrl || !$this->brokerName || !$this->brokerSecret) {
            throw new MissingConfigurationException('Missing configuration values.');
        }
    }

    /**
     * Make Http redirect to SSO Server
     *
     * @param string $command Request command name.
     * @param string $returnUrl Return URL
     * @return void
     */
    public function redirectRequest(string $command, string $returnUrl)
    {
        $this->saveReturnUrl($returnUrl);
        $parameters = $this->makeParameters($command, ['return_url' => config('app.url') . route('auth-callback', [], false)]);
        $this->redirect($this->generateCommandUrl($command, $parameters));
    }

    /**
     * Make parameters for request
     * 
     * @param string $command Request command name.
     * @param array $parameters Parameters for URL query string
     * @return array
     */
    protected function makeParameters(string $command = 'api/sso/v1/check', array $parameters = []) : array {
        $this->saveToken();
        $defaultParameters = [
            'broker' => $this->brokerName,
            'token' => $this->token,
            'hash' => Hash::make($command . $this->token . $this->brokerSecret),
            'command' => $command,
        ];
        return array_merge($defaultParameters, $parameters);
    }

    /**
     * Check response hash
     * 
     * @param array $parameters Recieved parameters
     * @param string $hash Received hash
     * @return boolean
     */
    public function checkResponse(array $parameters, string $hash) : bool {
        $this->saveToken();
        return Hash::check(implode($parameters) . $this->token . $this->brokerSecret, $hash);
    }

    /**
     * Make request to SSO server.
     *
     * @param string $method Request method 'post' or 'get'.
     * @param string $command Request command name.
     * @param array $parameters Parameters for URL query string if GET request and form parameters if it's POST request.
     *
     * @return Illuminate\Http\Client\Response
     */
    public function makeRequest(string $command, string $method = 'post', array $parameters = [])
    {
        $parameters = $this->makeParameters($command, $parameters);
        $commandUrl = $this->generateCommandUrl($command);

        $response = Http::acceptJson()->withToken($this->authToken)->{Str::lower($method)}($commandUrl, $parameters);

        return $response;
    }

    /**
     * Generate request url.
     *
     * @param string $command
     * @param array $parameters
     *
     * @return string
     */
    protected function generateCommandUrl(string $command, array $parameters = [])
    {
        $query = '';
        if (!empty($parameters)) {
            $query = '?' . http_build_query($parameters);
        }

        return $this->ssoServerUrl . '/' . $command . $query;
    }

    /**
     * Redirect client to specified url.
     *
     * @param string $url URL to be redirected.
     * @param array $parameters HTTP query string.
     * @param int $httpResponseCode HTTP response code for redirection.
     *
     * @return void
     */
    protected function redirect(string $url, array $parameters = [], int $httpResponseCode = 307)
    {
        $query = '';
        // Making URL query string if parameters given.
        if (!empty($parameters)) {
            $query = '?';

            if (parse_url($url, PHP_URL_QUERY)) {
                $query = '&';
            }

            $query .= http_build_query($parameters);
        }

        app()->abort($httpResponseCode, '', ['Location' => $url . $query]);
    }

    /**
     * Cookie name in which we save unique client token.
     *
     * @return string
     */
    protected function getCookieName()
    {
        // Cookie name based on broker's name because there can be some brokers on same domain
        // and we need to prevent duplications.
        return 'sso_token_' . preg_replace('/[_\W]+/', '_', strtolower($this->brokerName));
    }

    /**
     * Save unique client token to cookie.
     *
     * @return void
     */
    protected function saveToken()
    {
        if (isset($this->token) && $this->token) {
            return;
        }

        if ($this->token = Cookie::get($this->getCookieName(), null)) {
            return;
        }

        // If cookie token doesn't exist, we need to create it with unique token...
        $this->token = Str::random(40);
        Cookie::queue($this->getCookieName(), $this->token, 60);
    }
    /**
     * Save return url to cookie
     *
     * @param string $url Return URL
     * @return void
     */
    protected function saveReturnUrl(string $url)
    {
        /* $parsedUrl = parse_url($url);
        $url = (isset($parsedUrl['path']) ? '?' . $parsedUrl['path'] : '')
              .(isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '')
              .(isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : ''); */
        Cookie::queue(Cookie::make($this->getCookieName() . '_url', $url, 60));
    }
    /**
     * Get return URL from cookie
     *
     * @return string Return URL
     */
    public function getReturnUrl(): string
    {
        return Cookie::get($this->getCookieName() . '_url', '/');
    }

    /**
     * Delete saved unique client token and return URL.
     *
     * @return void
     */
    public function deleteToken()
    {
        $this->token = null;
        Cookie::expire($this->getCookieName());
        Cookie::expire($this->getCookieName() . '_url');
    }
}
