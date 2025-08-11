<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Config;
use BO\Zmsentities\Useraccount;

/**
 * Service class for handling OAuth-related HTTP requests
 * This service centralizes OAuth HTTP calls that were previously in zmsslim
 */
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
        return $this->http->readGetResult('/config/', [], $this->configSecureToken)->getEntity();
    }

    /**
     * Authenticate OAuth user with workstation
     *
     * @param Useraccount $ownerInputData
     * @param string|null $state
     * @return mixed
     */
    public function authenticateWorkstation(Useraccount $ownerInputData, ?string $state = null)
    {
        $headers = [];
        if ($state) {
            $headers['state'] = $state;
        }

        return $this->http->readPostResult('/workstation/oauth/', $ownerInputData, $headers)->getEntity();
    }
}
