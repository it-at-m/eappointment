<?php

namespace BO\Zmsclient;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * OAuth Business Logic moved from ZMS-Slim Middleware
 * Handles OAuth authentication workflow
 */
class OAuth
{
    protected $http;
    protected $auth;

    public function __construct(Http $http, Auth $auth)
    {
        $this->http = $http;
        $this->auth = $auth;
    }

    /**
     * Process OAuth login workflow
     *
     * @param array $ownerInputData Resource owner data from OAuth provider
     * @param string $state Authentication state
     * @return \BO\Zmsclient\Result
     * @throws \BO\Zmsclient\Exception
     */
    public function processOAuthLogin(array $ownerInputData, string $state)
    {
        if (class_exists('App') && isset(\App::$log)) {
            \App::$log->info('Processing OAuth login', [
                'event' => 'oauth_login_process',
                'timestamp' => date('c')
            ]);
        }

        try {
            // Send OAuth data to workstation endpoint
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

    /**
     * Clear existing session if needed
     */
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
}
