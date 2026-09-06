<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Config;
use BO\Zmsentities\Useraccount;

class OAuthService
{
    protected Http $http;
    private string $configSecureToken;

    public function __construct(Http $http, string $configSecureToken)
    {
        $this->http = $http;
        $this->configSecureToken = $configSecureToken;
    }

    /**
     * Retrieve configuration with secure token
     *
     * @return Config
     */
    public function readConfig(): Config
    {
        $entity = $this->http->readGetResult('/config/', [], $this->configSecureToken)->getEntity();
        if (!$entity instanceof Config) {
            throw new \RuntimeException('Config lookup returned no config entity');
        }
        return $entity;
    }

    /**
     * Authenticate OAuth user with workstation
     *
     * @param Useraccount $ownerInputData
     * @param string|null $state
     * @return mixed
     * @psalm-api
     */
    public function authenticateWorkstation(Useraccount $ownerInputData, ?string $state = null)
    {
        $headers = [];
        if ($state !== null && $state !== '') {
            $headers['state'] = $state;
        }

        return $this->http->readPostResult('/workstation/oauth/', $ownerInputData, $headers)->getEntity();
    }
}
