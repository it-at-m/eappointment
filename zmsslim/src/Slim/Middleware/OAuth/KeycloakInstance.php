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
        $this->logger = \App::$log;
        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function doLogin(ServerRequestInterface $request, ResponseInterface $response)
    {
        \App::$log->info('OIDC login attempt', [
            'event' => 'oauth_login_start',
            'timestamp' => date('c')
        ]);

        try {
            $accessToken = $this->getAccessToken($request->getParam("code"));
            $this->testAccess($accessToken);
            $ownerInputData = $this->provider->getResourceOwnerData($accessToken);
            $this->testOwnerData($ownerInputData);

            if (\BO\Zmsclient\Auth::getKey()) {
                \App::$log->info('Clearing existing session', [
                    'event' => 'oauth_session_clear',
                    'timestamp' => date('c')
                ]);
                $this->writeDeleteSession();
            }

            $this->writeTokenToSession($accessToken);
            \App::$http
                ->readPostResult('/workstation/oauth/', $ownerInputData, ['state' => \BO\Zmsclient\Auth::getKey()])
                ->getEntity();

            \App::$log->info('OIDC login successful', [
                'event' => 'oauth_login_success',
                'timestamp' => date('c')
            ]);
        } catch (\BO\Zmsclient\Exception $exception) {
            $this->logger->error('OIDC login failed', [
                'event' => 'oauth_login_error',
                'timestamp' => date('c'),
                'error' => $exception->getMessage()
            ]);
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
        \App::$log->info('Validating OIDC token', [
            'event' => 'oauth_token_validation',
            'timestamp' => date('c')
        ]);

        list($header, $payload, $signature) = explode('.', $token->getToken());

        if (empty($header)) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_header'
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }
        if (empty($payload)) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_payload'
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }
        if (empty($signature)) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_signature'
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $accessTokenPayload = json_decode(base64_decode($payload), true);
        $clientRoles = array();

        if ($accessTokenPayload === null) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_payload_json',
                'error' => json_last_error_msg()
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        if (!isset($accessTokenPayload['resource_access']) || !is_array($accessTokenPayload['resource_access'])) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_resource_access',
                'has_resource_access' => isset($accessTokenPayload['resource_access']),
                'resource_access_type' => gettype($accessTokenPayload['resource_access'] ?? null)
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        if (!isset($accessTokenPayload['resource_access'][\App::IDENTIFIER])) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_app_identifier',
                'app_identifier' => \App::IDENTIFIER,
                'available_resources' => array_keys($accessTokenPayload['resource_access'])
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        $resourceAccess = $accessTokenPayload['resource_access'];
        $appIdentifierRoles = $resourceAccess[\App::IDENTIFIER]['roles'] ?? null;

        if (!$appIdentifierRoles || !is_array($appIdentifierRoles)) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'invalid_roles',
                'has_roles' => isset($resourceAccess[\App::IDENTIFIER]['roles']),
                'roles_type' => gettype($appIdentifierRoles)
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        if (is_array($accessTokenPayload['resource_access'])) {
            $clientRoles = array_values($accessTokenPayload['resource_access'][\App::IDENTIFIER]['roles']);
        }
            
        if (!in_array($realmData['accessRole'], $clientRoles)) {
            $this->logger->error('Token validation failed', [
                'event' => 'oauth_token_validation_failed',
                'timestamp' => date('c'),
                'reason' => 'missing_required_role',
                'required_role' => $realmData['accessRole'],
                'available_roles' => $clientRoles
            ]);
            throw new \BO\Slim\Exception\OAuthFailed();
        }

        \App::$log->info('Token validation successful', [
            'event' => 'oauth_token_validation_success',
            'timestamp' => date('c')
        ]);
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
        \App::$log->info('Getting access token', [
            'event' => 'oauth_get_token',
            'timestamp' => date('c')
        ]);

        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            \App::$log->info('Access token obtained', [
                'event' => 'oauth_get_token_success',
                'timestamp' => date('c')
            ]);
            return $accessToken;
        } catch (\Exception $exception) {
            $this->logger->error('Failed to get access token', [
                'event' => 'oauth_get_token_error',
                'timestamp' => date('c'),
                'error' => $exception->getMessage(),
                'exception_class' => get_class($exception)
            ]);
            if ('League\OAuth2\Client\Provider\Exception\IdentityProviderException' === get_class($exception)) {
                throw new \BO\Slim\Exception\OAuthFailed();
            }
            throw $exception;
        }
    }

    private function writeTokenToSession($token)
    {
        \App::$log->info('Writing token to session', [
            'event' => 'oauth_write_token',
            'timestamp' => date('c')
        ]);

        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->write(\BO\Zmsclient\Auth::getKey(), serialize($token), ['oidc' => true]);
        return $sessionHandler->close();
    }

    private function writeDeleteSession()
    {
        \App::$log->info('Deleting session', [
            'event' => 'oauth_delete_session',
            'timestamp' => date('c')
        ]);

        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $sessionHandler->destroy(\BO\Zmsclient\Auth::getKey());
    }

    private function readTokenDataFromSession()
    {
        \App::$log->info('Reading token from session', [
            'event' => 'oauth_read_token',
            'timestamp' => date('c')
        ]);

        $realmData = $this->provider->getBasicOptionsFromJsonFile();
        $sessionHandler = (new \BO\Zmsclient\SessionHandler(\App::$http));
        $sessionHandler->open('/'. $realmData['realm'] . '/', $realmData['clientId']);
        $tokenData = unserialize($sessionHandler->read(\BO\Zmsclient\Auth::getKey(), ['oidc' => true]));
        return $tokenData;
    }
}
