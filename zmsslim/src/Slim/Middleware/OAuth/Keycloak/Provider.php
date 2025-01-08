<?php

namespace BO\Slim\Middleware\OAuth\Keycloak;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use BO\Zmsclient\Psr7\ClientInterface as HttpClientInterface;
use BO\Zmsclient\PSR7\Client;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @SuppressWarnings(PHPMD)
 */

class Provider extends Keycloak
{
    const PROVIDERNAME = 'keycloak';

    /**
     * Sets the config options for keycloak access from json file.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, `authServerurl` and `realm`.
     *     Individual providers may introduce more options, as needed.
     * @return parent
     */
    public function __construct($client = null)
    {
        $client = ((null === $client)) ? new Client() : $client;
        $options = $this->getOptionsFromJsonFile();
        return parent::__construct($options, ['httpClient' => $client]);
    }

    /**
     * Sets the HTTP client instance.
     *
     * @param  HttpClientInterface $client
     * @return self
     */
    public function setHttpClient($client)
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
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ResourceOwner($response);
    }

    /**
     * Requests and returns the resource owner data of given access token.
     *
     * @param  AccessToken $token
     * @return Array
     */
    public function getResourceOwnerData(AccessToken $token)
    {
        $resourceOwner = $this->getResourceOwner($token);
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $ownerData['username'] = $resourceOwner->getName() . '@' . static::PROVIDERNAME;
        if (1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
            if ($resourceOwner->getVerifiedEmail()) {
                $ownerData['email'] = $resourceOwner->getVerifiedEmail();
            }
        } else {
            $ownerData['email'] = $resourceOwner->getEmail();
        }
        return $ownerData;
    }

    private function getOptionsFromJsonFile()
    {
        $config_data = file_get_contents(\App::APP_PATH . '/' . static::PROVIDERNAME . '.json');
        if (gettype($config_data) === 'string') {
            $config_data = json_decode($config_data, true);
        }
        $realmData = $this->getBasicOptionsFromJsonFile();
        $realmData['clientSecret'] = $config_data['credentials']['secret'];
        $realmData['authServerUrl'] = $config_data['auth-server-url'];
        $realmData['verify'] = $config_data['ssl-verify'];
        return $realmData;
    }

    public function getBasicOptionsFromJsonFile()
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
