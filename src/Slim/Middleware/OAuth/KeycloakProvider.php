<?php

namespace BO\Slim\Middleware\OAuth;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use BO\Zmsclient\Psr7\ClientInterface as HttpClientInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class KeycloakProvider extends Keycloak
{
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
}
