<?php

namespace BO\Zmsclient;

class OAuth
{
    protected $http;
    protected $auth;

    public function __construct(Http $http, Auth $auth)
    {
        $this->http = $http;
        $this->auth = $auth;
    }

    public function processOAuthLogin(array $ownerInputData, string $state)
    {
        if (class_exists('App') && isset(\App::$log)) {
            \App::$log->info('Processing OAuth login', [
                'event' => 'oauth_login_process',
                'timestamp' => date('c')
            ]);
        }

        try {
            $result = $this->http->readPostResult('/workstation/oauth/', $ownerInputData, ['state' => $state]);

            if (class_exists('App') && isset(\App::$log)) {
                \App::$log->info('OAuth login successful', [
                    'event' => 'oauth_login_success',
                    'timestamp' => date('c')
                ]);
            }

            return $result;
        } catch (\BO\Zmsclient\Exception $exception) {
            if (class_exists('App') && isset(\App::$log)) {
                \App::$log->error('OAuth login failed', [
                    'event' => 'oauth_login_error',
                    'timestamp' => date('c'),
                    'error' => $exception->getMessage()
                ]);
            }
            throw $exception;
        }
    }

    public function clearExistingSession()
    {
        if (Auth::getKey()) {
            if (class_exists('App') && isset(\App::$log)) {
                \App::$log->info('Clearing existing session', [
                    'event' => 'oauth_session_clear',
                    'timestamp' => date('c')
                ]);
            }
        }
    }

    public function validateOwnerData(array $ownerInputData)
    {
        if (class_exists('App') && isset(\App::$http)) {
            $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
            if (! \array_key_exists('email', $ownerInputData) && 1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
                throw new \BO\Zmsclient\Exception('OAuth precondition failed: email required but not provided');
            }
        }
    }

    public function processResourceOwnerData(array $rawOwnerData)
    {
        $ownerData = $rawOwnerData;

        if (class_exists('App') && isset(\App::$http)) {
            $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();

            if (1 == $config->getPreference('oidc', 'onlyVerifiedMail')) {
                if (isset($rawOwnerData['verifiedEmail']) && $rawOwnerData['verifiedEmail']) {
                    $ownerData['email'] = $rawOwnerData['verifiedEmail'];
                } else {
                    unset($ownerData['email']);
                }
            } else {
                $ownerData['email'] = $rawOwnerData['email'] ?? null;
            }
        }

        return $ownerData;
    }
}
