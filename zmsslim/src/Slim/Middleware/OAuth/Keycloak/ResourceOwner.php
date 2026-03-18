<?php

namespace BO\Slim\Middleware\OAuth\Keycloak;

use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;

class ResourceOwner extends KeycloakResourceOwner
{
    /**
     * Raw response
     *
     * @var array<string, mixed>
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array<string, mixed>  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     */
    public function getId(): ?string
    {
        return \array_key_exists('sub', $this->response) ? $this->response['sub'] : null;
    }

    /**
     * Get resource owner email
     */
    public function getEmail(): ?string
    {
        return \array_key_exists('email', $this->response) ? $this->response['email'] : null;
    }

    /**
     * Get resource owner name
     */
    public function getName(): ?string
    {
        return \array_key_exists('preferred_username', $this->response) ? $this->response['preferred_username'] : null;
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
