<?php

namespace BO\Slim\Middleware\OAuth;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \BO\Zmsentities\Useraccount as UseraccountEntity;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakAuth
{
    protected $provider = null;

    public function __construct()
    {
        $this->provider = new KeycloakProvider();
        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function doLogin(ServerRequestInterface $request, ResponseInterface $response){
        $accessToken = $this->getAccessToken($request->getParam("code"));
        $useraccount = $this->getUseraccountByAccessToken($accessToken);
        try {
            $this->writeTokenToSession($accessToken);
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
        $realmData = $this->provider::getBasicOptionsFromJsonFile();
        $logoutUrl = $this->provider->getLogoutUrl(['redirect_uri' => $realmData['logoutUri']]);
        return $response->withRedirect($logoutUrl, 301);
    }

    public function writeNewAccessTokenIfExpired(ResponseInterface $response)
    {   
        try {
            $accessTokenData = $this->readTokenDataFromSession();
            $accessTokenData = (is_array($accessTokenData)) ? $accessTokenData : [];
            $existingAccessToken = new AccessToken($accessTokenData);
            if ($existingAccessToken && $existingAccessToken->hasExpired()) {
                $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $existingAccessToken->getRefreshToken()
                ]);
                $this->writeDeleteSession();
                $this->writeTokenToSession($newAccessToken);
            }
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    private function getUseraccountByAccessToken($accessToken){
        $ownerData = $this->provider->getResourceOwner($accessToken);
        $useraccount = (new UseraccountEntity())->createFromOpenIdData($ownerData);
        return $useraccount;
    }

    private function getAccessToken($code)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
        } catch (\Exception $exception) {
            if ('League\OAuth2\Client\Provider\Exception\IdentityProviderException' === get_class($exception)) {
                throw new \BO\Slim\Exception\OAuthFailed();
            }
            throw $exception;
        }
        return $accessToken;
    }

    private function writeTokenToSession($token)
    {
        $realmData = $this->provider::getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->write(\BO\Zmsclient\Auth::getKey(), serialize($token), ['oidc' => true]);
        return $sessionHandler->close();
    }

    private function writeDeleteSession()
    {
        $realmData = $this->provider::getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->destroy(\BO\Zmsclient\Auth::getKey());
    }

    private function readTokenDataFromSession()
    {
        $realmData = $this->provider::getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $tokenData = unserialize($sessionHandler->read(\BO\Zmsclient\Auth::getKey(), ['oidc' => true]));
        return $tokenData;
    }
}
