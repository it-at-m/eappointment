<?php

namespace BO\Slim\Middleware\OAuth;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \BO\Zmsclient\PSR7\Client;
use \BO\Zmsclient\PSR7\ClientInterface;
use \BO\Zmsentities\Useraccount as UseraccountEntity;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakAuth
{
    protected $provider = null;
    protected $token = '';

    public function __construct(ClientInterface $client = null)
    {
        $client = ((null === $client)) ? new Client() : $client;
        $this->setProvider($client);
        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getUseraccount($code){
        $ownerData = $this->getAccessTokenOwnerData($code);
        $useraccount = (new UseraccountEntity())->createFromOpenIdData($ownerData);
        return $useraccount;
    }

    public function doLogin(ServerRequestInterface $request, ResponseInterface $response){
        $useraccount = $this->getUseraccount($request->getParam("code"));
        try {
            \App::$http
                ->readPostResult('/workstation/oauth/', $useraccount, ['state' => \BO\Zmsclient\Auth::getKey()])->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            \BO\Zmsclient\Auth::removeKey();
            throw $exception;
        }
        return $response;
    }

    public function doLogout(ResponseInterface $response) {
        $this->writeDeleteSession();
        $logoutUrl = $this->provider->getLogoutUrl(['redirect_uri' => \App::ZMS_LOGOUT_REDIRECTURI]);
        return $response->withRedirect($logoutUrl, 301);
    }

    public function writeNewAccessTokenIfExpired(ResponseInterface $response)
    {   
        try {
            $accessTokenData = $this->readTokenDataFromSession();
            $accessTokenData = (is_array($accessTokenData)) ? $accessTokenData : [];
            $existingAccessToken = new AccessToken($accessTokenData);
            if ($existingAccessToken->hasExpired()) {
                $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $existingAccessToken->getRefreshToken()
                ]);
                $this->writeDeleteSession();
                $this->writeTokenToSession($newAccessToken);
            }
        } catch (\Exception $exception) {
            return $this->doLogout($response);
        }
        return $response;
    }

    public function getToken()
    {
        return $this->token;
    }

    private function setProvider($client = null)
    {
        $this->provider = new KeycloakProvider([
            'authServerUrl'         => \App::ZMS_AUTHORIZATION_AUTHSERVERURL,
            'realm'                 => \App::ZMS_AUTHORIZATION_REALM,
            'clientId'              => \App::ZMS_AUTHORIZATION_CLIENT_ID,
            'clientSecret'          => \App::ZMS_AUTHORIZATION_CLIENT_SECRET,
            'redirectUri'           => \App::ZMS_AUTHORIZATION_REDIRECTURI,
        ], ['httpClient' => $client]);
    }

    private function getAccessTokenOwnerData($code)
    {
        try {
            $token = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            $this->token = $token->getToken();
            $this->writeTokenToSession($token);
        } catch (Exception $exception) {
            throw $exception;
        }
        $accessTokenOwner = $this->provider->getResourceOwner($token);
        return $accessTokenOwner;
    }

    private function writeTokenToSession($token)
    {
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. \App::ZMS_AUTHORIZATION_REALM . '/', \App::ZMS_AUTHORIZATION_CLIENT_ID);
        $sessionHandler->write(\BO\Zmsclient\Auth::getKey(), serialize($token), ['oidc' => true]);
        return $sessionHandler->close();
    }

    private function writeDeleteSession()
    {
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. \App::ZMS_AUTHORIZATION_REALM . '/', \App::ZMS_AUTHORIZATION_CLIENT_ID);
        $sessionHandler->destroy(\BO\Zmsclient\Auth::getKey());
    }

    private function readTokenDataFromSession()
    {
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. \App::ZMS_AUTHORIZATION_REALM . '/', \App::ZMS_AUTHORIZATION_CLIENT_ID);
        return unserialize($sessionHandler->read(\BO\Zmsclient\Auth::getKey(), ['oidc' => true]));
    }
}
