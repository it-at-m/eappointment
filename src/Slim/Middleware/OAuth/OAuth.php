<?php

namespace BO\Slim\Middleware\OAuth;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use GuzzleHttp\Client;//TODO: remove
use \BO\Slim\Profiler as Profiler;

class OAuth
{
    private $provider = null;
    private $clientRoles = [];
    private $userData = [];

    public function __construct() {
        $this->setProvider();
    }

    private function setProvider(){
        $client = new Client([
            'defaults' => [
                \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => \App::ZMS_AUTHORIZATION_CONNECT_TIMEOUT,
                \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true
            ],
            \GuzzleHttp\RequestOptions::VERIFY => \App::ZMS_AUTHORIZATION_SSL_VERIFY,
        ]); //TODO: use zmsclient
        //$client = \App::$http = new \BO\Zmsclient\Http(\App::HTTP_BASE_URL); \BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;
        //$client = \BO\Zmsclient\Psr7\Client::getClient(\App::$http_curl_config);
        $this->provider = new Keycloak([
            'authServerUrl'         => \App::ZMS_AUTHORIZATION_AUTHSERVERURL,
            'realm'                 => \App::ZMS_AUTHORIZATION_REALM,
            'clientId'              => \App::ZMS_AUTHORIZATION_CLIENT_ID,
            'clientSecret'          => \App::ZMS_AUTHORIZATION_CLIENT_SECRET,
            'redirectUri'           => \App::ZMS_AUTHORIZATION_REDIRECTURI,
        ]);
        $this->provider->setHttpClient($client);
    }

    public function getUserData(){
        return $this->userData;
    }

    public function setAccessTokenPayload($code, $state){
        if (!isset($code)) {
            $this->getAuthorizationCode();
        }

        $this->testState($state);
        $token = $this->getAccessToken($code);

        list($header, $payload, $signature)  = explode('.', $token->getToken());
        $accessTokenPayload = json_decode(base64_decode($payload), true);
        $this->userData['preferred_username'] = $accessTokenPayload['preferred_username'];
        $this->userData['email'] = $accessTokenPayload['email'];
        $this->clientRoles = $accessTokenPayload['resource_access'][\App::ZMS_AUTHORIZATION_CLIENT_ID]['roles'];
    }

    private function getAuthorizationCode(){
        $authUrl = $this->provider->getAuthorizationUrl();
        \BO\Zmsclient\Auth::setKey($this->provider->getState());
        header('Location: ' . $authUrl);
        exit;
    }

    private function testState($state){
        if (empty($state) || ($state !== \BO\Zmsclient\Auth::getKey())) {
            \BO\Zmsclient\Auth::removeKey();
            throw new \Exception('Invalid state.');
        }
    }

    public function getAccessToken($code){
        try {
            Profiler::add("Start AccessToken");
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            Profiler::add("End AccessToken");
        } catch (Exception $e) {
            throw new \Exception('Failed to get access token: '.$e->getMessage());
        }

        return $token;
    }

    public function testAccessRight(){
        return in_array( \App::ZMS_AUTHORIZATION_ACCESS_ROLE ,$this->clientRoles );
    }
}
