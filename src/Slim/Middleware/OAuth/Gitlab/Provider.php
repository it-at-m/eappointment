<?php

namespace BO\Slim\Middleware\OAuth\Gitlab;

use \BO\Zmsclient\Psr7\ClientInterface as HttpClientInterface;
use \BO\Zmsclient\Psr7\Client;
use League\OAuth2\Client\Tool\Request;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use Exception;

/**
 * @SuppressWarnings(PHPMD)
 */

class Provider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const PROVIDERNAME = 'gitlab';

    /**
     * auth URL, eg. http://localhost:8080/auth.
     *
     * @var string
     */
    public $authServerUrl = null;

    /**
     * Sets the config options for gitlab access from json file.
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
        $this->authServerUrl = $options['authServerUrl'];
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
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseUrl() . '/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseUrl() . '/token';
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseLogoutUrl()
    {
        return $this->getBaseUrl() . '/revoke';
    }

    /**
     * Builds the revoke URL.
     *
     * @param AccessToken $token
     * @return ResponseInterface $response
     */
    public function getRevokeResponse($token)
    {
        $url = $this->getBaseLogoutUrl();
        $options = $this->getPostAuthOptions($token);
        $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token, $options);
        $response = $this->getResponse($request);
        return $response;
    }

    /**
     * get post body for authentification on revoke endpoint
     *
     * @param AccessToken $token
     * @return array $options
     */
    public function getPostAuthOptions($token)
    {
        $realmData = $this->getOptionsFromJsonFile();
        $options['client_secret'] = $realmData['clientSecret'];
        $options['client_id'] = $realmData['clientId'];
        $options['token'] = $realmData[$token->getToken()];
        return $options;
    }

    /**
     * Creates base url from provider configuration.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->authServerUrl;
    }

    private function getOptionsFromJsonFile()
    {
        $config_data = file_get_contents(\App::APP_PATH . '/'. static::PROVIDERNAME .'.json');
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
        $config_data = file_get_contents(\App::APP_PATH . '/'. static::PROVIDERNAME .'.json');
        if (gettype($config_data) === 'string') {
            $config_data = json_decode($config_data, true);
        }
        $realmData['realm'] = $config_data['realm'];
        $realmData['clientId'] = $config_data['clientId'];
        $realmData['clientName'] = $config_data['clientName'];
        $realmData['redirectUri'] = $config_data['auth-redirect-url'];
        $realmData['logoutUri'] = $config_data['logout-redirect-url'];
        return $realmData;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseUrl().'/userinfo';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return string[]
     */
    protected function getDefaultScopes()
    {
        return ['profile', 'email', 'read_user', 'openid'];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator()
    {
        return ' ';
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
        $ownerData['username'] = $resourceOwner->getName(). '@' . static::PROVIDERNAME;
        if ($resourceOwner->getVerifiedEmail()) {
            $ownerData['email'] = $resourceOwner->getVerifiedEmail();
        }
        return $ownerData;
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwner
     * @throws EncryptionConfigurationException
     */
    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);
        if (array_key_exists('jwt', $response)) {
            $response = $response['jwt'];
        }
        return $this->createResourceOwner($response, $token);
    }

    /**
     * Requests resource owner details.
     *
     * @param  AccessToken $token
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);
        $response = $this->getParsedResponse($request);
        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        return $response;
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];
            if (isset($data['error_description'])) {
                $error.=': '.$data['error_description'];
            }
            throw new IdentityProviderException($error, 0, $data);
        }
    }

    /**
     * Parses the response according to its content-type header.
     *
     * @throws UnexpectedValueException
     * @param  ResponseInterface $response
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        // We have a problem with keycloak when the userinfo responses
        // with a jwt token
        // Because it just return a jwt as string with the header
        // application/jwt
        // This can't be parsed to a array
        // Dont know why this function only allow an array as return value...
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (strpos($type, 'jwt') !== false) {
            // Here we make the temporary array
            return ['jwt' => $content];
        }

        return parent::parseResponse($response);
    }
}
