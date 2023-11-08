<?php

namespace BO\Slim\Middleware\OAuth\Keycloak;

use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;

class ResourceOwner extends KeycloakResourceOwner
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        return \array_key_exists('sub', $this->response) ? $this->response['sub'] : null;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return \array_key_exists('email', $this->response) ? $this->response['email'] : null;
    }

    /**
     * Get verified resource owner email
     *
     * @return string|null
     */
    public function getVerifiedEmail()
    {
        $email = null;
        if (\array_key_exists('email_verified', $this->response) && $this->response['email_verified']) {
            $email = $this->getEmail();
        }
        return $email;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName()
    {
        return \array_key_exists('preferred_username', $this->response) ? $this->response['preferred_username'] : null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
