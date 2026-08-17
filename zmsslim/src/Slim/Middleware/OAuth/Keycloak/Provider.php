<?php

namespace BO\Slim\Middleware\OAuth\Keycloak;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use BO\Zmsclient\OAuthService;
use BO\Zmsclient\Psr7\Client;
use League\OAuth2\Client\Token\AccessToken;
use BO\Zmsentities\Useraccount;

/**
 * @SuppressWarnings(PHPMD)
 */
class Provider extends Keycloak
{
    const string PROVIDERNAME = 'keycloak';

    protected OAuthService $oauthService;

    public function __construct(mixed $client = null, ?OAuthService $oauthService = null)
    {
        $this->oauthService = $oauthService ?? new OAuthService(\App::$http, \App::CONFIG_SECURE_TOKEN);
        $client = $client ?? new Client();
        $options = $this->getOptionsFromJsonFile();
        parent::__construct($options, ['httpClient' => $client]);
    }

    #[\Override]
    public function setHttpClient($client)
    {
        $this->httpClient = $client;
        return $this;
    }

    #[\Override]
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwner
    {
        return new ResourceOwner($response);
    }

    public function getResourceOwnerData(AccessToken $token): Useraccount
    {
        $resourceOwner = $this->getResourceOwner($token);
        $config = $this->oauthService->readConfig();
        $ownerData = [
            'username' => ($resourceOwner->getName() ?? '') . '@' . static::PROVIDERNAME,
        ];
        if (1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
            $email = $resourceOwner->getEmail();
            $ownerArray = $resourceOwner->toArray();
            $emailVerified = $ownerArray['email_verified'] ?? false;
            if (is_string($email) && $email !== '' && $emailVerified === true) {
                $ownerData['email'] = $email;
            }
        } else {
            $ownerData['email'] = $resourceOwner->getEmail();
        }
        return new Useraccount($ownerData);
    }

    private function getOptionsFromJsonFile(): array
    {
        $configData = $this->readKeycloakConfig();
        $realmData = $this->getBasicOptionsFromJsonFile();
        $realmData['clientSecret'] = $configData['credentials']['secret'] ?? '';
        $realmData['authServerUrl'] = $configData['auth-server-url'] ?? '';
        $realmData['verify'] = $configData['ssl-verify'] ?? true;
        return $realmData;
    }

    public function getBasicOptionsFromJsonFile(): array
    {
        $configData = $this->readKeycloakConfig();
        return [
            'realm' => $configData['realm'] ?? '',
            'clientId' => $configData['clientId'] ?? '',
            'clientName' => $configData['clientName'] ?? '',
            'redirectUri' => $configData['auth-redirect-url'] ?? '',
            'logoutUri' => $configData['logout-redirect-url'] ?? '',
            'version' => $configData['version'] ?? '',
            'accessRole' => $configData['access-role'] ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readKeycloakConfig(): array
    {
        $configJson = file_get_contents(\App::APP_PATH . '/' . static::PROVIDERNAME . '.json');
        if (!is_string($configJson)) {
            throw new \RuntimeException('Unable to read keycloak.json');
        }
        $configData = json_decode($configJson, true);
        if (!is_array($configData)) {
            throw new \RuntimeException('Invalid keycloak.json');
        }
        return $configData;
    }
}
