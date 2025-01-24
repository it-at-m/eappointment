<?php

namespace BO\Slim\Middleware\OAuth;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @SuppressWarnings(PHPMD)
 */

class KeycloakInstance
{
    protected $provider = null;

    public function __construct()
    {
        $this->provider = new Keycloak\Provider();
        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function doLogin(ServerRequestInterface $request, ResponseInterface $response)
    {
        $accessToken = $this->getAccessToken($request->getParam("code"));
        $this->testAccess($accessToken);
        $ownerInputData = $this->provider->getResourceOwnerData($accessToken);
        $this->testOwnerData($ownerInputData);
        try {
            if (\BO\Zmsclient\Auth::getKey()) {
                $this->writeDeleteSession();
            }
            $this->writeTokenToSession($accessToken);
            \App::$http
                ->readPostResult('/workstation/oauth/', $ownerInputData, ['state' => \BO\Zmsclient\Auth::getKey()])
                ->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            $this->writeDeleteSession();
            \BO\Zmsclient\Auth::removeKey();
            \BO\Zmsclient\Auth::removeOidcProvider();
            throw $exception;
        }
        return $response;
    }

    public function doLogout(ResponseInterface $response)
    {
        $this->writeDeleteSession();
        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        return $response->withRedirect($realmData['logoutUri'], 301);
    }

    public function writeNewAccessTokenIfExpired()
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

    private function testAccess(AccessToken $token)
    {
        list($header, $payload, $signature)  = explode('.', $token->getToken());

        // Ensure header, payload, and signature exist
        if (empty($header)) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_header'
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
        if (empty($payload)) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_payload'
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
        if (empty($signature)) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_signature'
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $accessTokenPayload = json_decode(base64_decode($payload), true);
        $clientRoles = array();

        // Ensure that the payload is correctly decoded
        if ($accessTokenPayload === null) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_payload_json',
                'error' => json_last_error_msg()
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
    
        // Checking for 'resource_access' and ensuring it's an array
        if (!isset($accessTokenPayload['resource_access']) || !is_array($accessTokenPayload['resource_access'])) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_resource_access',
                'has_resource_access' => isset($accessTokenPayload['resource_access']),
                'resource_access_type' => gettype($accessTokenPayload['resource_access'] ?? null)
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
    
        // Checking if App Identifier exists
        if (!isset($accessTokenPayload['resource_access'][\App::IDENTIFIER])) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_app_identifier',
                'app_identifier' => \App::IDENTIFIER,
                'available_resources' => array_keys($accessTokenPayload['resource_access'])
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        // Checking if roles exist for the app identifier
        $resourceAccess = $accessTokenPayload['resource_access'];
        $appIdentifierRoles = $resourceAccess[\App::IDENTIFIER]['roles'] ?? null;

        if (!$appIdentifierRoles || !is_array($appIdentifierRoles)) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_roles',
                'has_roles' => isset($resourceAccess[\App::IDENTIFIER]['roles']),
                'roles_type' => gettype($appIdentifierRoles)
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
    
        if (is_array($accessTokenPayload['resource_access'])) {
            $clientRoles = array_values($accessTokenPayload['resource_access'][\App::IDENTIFIER]['roles']);
        }
            
        if (!in_array($realmData['accessRole'], $clientRoles)) {
            error_log(json_encode([
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_required_role',
                'required_role' => $realmData['accessRole'],
                'available_roles' => $clientRoles
            ]));
            throw new \BO\Slim\Exception\OAuthFailed();
        }
    }

    private function testOwnerData(array $ownerInputData)
    {
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        if (! \array_key_exists('email', $ownerInputData) && 1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
            throw new \BO\Slim\Exception\OAuthPreconditionFailed();
        }
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
        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->write(\BO\Zmsclient\Auth::getKey(), serialize($token), ['oidc' => true]);
        return $sessionHandler->close();
    }

    private function writeDeleteSession()
    {
        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->destroy(\BO\Zmsclient\Auth::getKey());
    }

    private function readTokenDataFromSession()
    {
        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $tokenData = unserialize($sessionHandler->read(\BO\Zmsclient\Auth::getKey(), ['oidc' => true]));
        return $tokenData;
    }
}
