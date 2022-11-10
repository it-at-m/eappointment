<?php

namespace BO\Slim\Middleware\OAuth;

use \Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use \BO\Zmsclient\Psr7\ClientInterface as HttpClientInterface;
use \BO\Zmsclient\PSR7\Client;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class KeycloakProvider extends Keycloak
{
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

    public static function getBasicOptionsFromJsonFile()
    {
        $config_data = file_get_contents(\App::APP_PATH . '/keycloak.json');
        if (gettype($config_data) === 'string') {
			$config_data = json_decode($config_data, TRUE);
		}
		$realmData['realm'] = $config_data['realm'];
		$realmData['clientId'] = array_key_exists('resource', $config_data) ? 
            $config_data['resource'] : 
            $config_data['client_id'];
        $realmData['redirectUri'] = $config_data['auth-redirect-url'] ? 
            $config_data['auth-redirect-url'] : 
            'http://localhost';
        $realmData['logoutUri'] = $config_data['logout-redirect-url'] ? 
            $config_data['logout-redirect-url'] : 
            'http://localhost';
        return $realmData;
    }

    private function getOptionsFromJsonFile()
    {
        $config_data = file_get_contents(\App::APP_PATH . '/keycloak.json');
        if (gettype($config_data) === 'string') {
			$config_data = json_decode($config_data, TRUE);
		}
		$realmData['realm'] = $config_data['realm'];
		$realmData['clientId'] = array_key_exists('resource', $config_data) ? 
            $config_data['resource'] : 
            $config_data['client_id'];
        $realmData['clientSecret'] = array_key_exists('credentials', $config_data) ? 
            $config_data['credentials']['secret'] : 
            (array_key_exists('secret', $config_data) ? 
                $config_data['secret'] : 
                NULL
            );
        $realmData['authServerUrl'] = $config_data['auth-server-url'] ? 
            $config_data['auth-server-url'] : 
            'http://localhost';
        $realmData['redirectUri'] = $config_data['auth-redirect-url'] ? 
            $config_data['auth-redirect-url'] : 
            'http://localhost';
        $realmData['logoutUri'] = $config_data['logout-redirect-url'] ? 
            $config_data['logout-redirect-url'] : 
            'http://localhost';
        return $realmData;
    }
}
