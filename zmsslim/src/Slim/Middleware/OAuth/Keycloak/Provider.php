<?php

namespace BO\Slim\Middleware\OAuth\Keycloak;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use BO\Zmsclient\PSR7\Client;
use League\OAuth2\Client\Token\AccessToken;
use GuzzleHttp\ClientInterface;
use BO\Zmsentities\Useraccount;

/**
 * @SuppressWarnings(PHPMD)
 */

class Provider extends Keycloak
{
    const PROVIDERNAME = 'keycloak';

    /**
     * @var \BO\Zmsclient\OAuthService
     */
    protected $oauthService;

    /**
     * Sets the config options for keycloak access from json file.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, `authServerurl` and `realm`.
     *     Individual providers may introduce more options, as needed.
     * @return parent
     */
    public function __construct($client = null, ?\BO\Zmsclient\OAuthService $oauthService = null)
    {
        $client = ((null === $client)) ? new Client() : $client;
        $this->oauthService = $oauthService ?: new \BO\Zmsclient\OAuthService(\App::$http);
        $options = $this->getOptionsFromJsonFile();
        parent::__construct($options, ['httpClient' => $client]);
    }

    /**
     * Sets the HTTP client instance.
     *
     * @param  ClientInterface $client
     * @return static
     */
    public function setHttpClient(ClientInterface $client): static
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return ResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwner
    {
        return new ResourceOwner($response);
    }

    /**
     * Requests and returns the resource owner data of given access token.
     *
     * @param  AccessToken $token
     * @return array
     */
    public function getResourceOwnerData(AccessToken $token): Useraccount
    {
        $resourceOwner = $this->getResourceOwner($token);
        $config = $this->oauthService->readConfig();
        $ownerData['username'] = $resourceOwner->getName() . '@' . static::PROVIDERNAME;
        if (1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
            $email = $resourceOwner->getEmail();
            if ($email && $resourceOwner->toArray()['email_verified'] ?? false) {
                $ownerData['email'] = $email;
            }
        } else {
            $ownerData['email'] = $resourceOwner->getEmail();
        }
        return new Useraccount($ownerData);
    }

    private function getOptionsFromJsonFile(): array
    {
        $config_data = file_get_contents(\App::APP_PATH . '/' . static::PROVIDERNAME . '.json');
        if (gettype($config_data) === 'string') {
            $config_data = json_decode($config_data, true);
        }
        $realmData = $this->getBasicOptionsFromJsonFile();
        $realmData['clientSecret'] = $config_data['credentials']['secret'];
        $realmData['authServerUrl'] = $config_data['auth-server-url'];
        $realmData['verify'] = $config_data['ssl-verify'] ?? true;
        return $realmData;
    }

    public function getBasicOptionsFromJsonFile(): array
    {
        $config_data = file_get_contents(\App::APP_PATH . '/' . static::PROVIDERNAME . '.json');
        if (gettype($config_data) === 'string') {
            $config_data = json_decode($config_data, true);
        }
        $realmData['realm'] = $config_data['realm'];
        $realmData['clientId'] = $config_data['clientId'];
        $realmData['clientName'] = $config_data['clientName'];
        $realmData['redirectUri'] = $config_data['auth-redirect-url'];
        $realmData['logoutUri'] = $config_data['logout-redirect-url'];
        $realmData['version'] = $config_data['version'];
        $realmData['accessRole'] = $config_data['access-role'];
        return $realmData;
    }
}
