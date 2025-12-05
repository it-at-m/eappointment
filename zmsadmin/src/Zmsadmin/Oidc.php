<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsclient\Auth;

class Oidc extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $state = $request->getParam("state");
            $authKey = Auth::getKey();
            $sessionHash = hash('sha256', $authKey);

            \App::$log->info('OIDC Login state validation', [
                'event' => 'oauth_login_state_validation',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => 'zmsadmin',
                'state_match' => ($state == $authKey),
                'hashed_session_token' => $sessionHash
            ]);

            if ($state == $authKey) {
                try {
                    $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
                    $username = $workstation->getUseraccount()->id;
                    $authkey = $workstation->authkey ?? Auth::getKey() ?? '';
                    $sessionHash = hash('sha256', $authkey);

                    \App::$log->info('OIDC Login workstation access', [
                        'event' => 'oauth_login_workstation_access',
                        'timestamp' => date('c'),
                        'provider' => Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'username' => $username,
                        'workstation_id' => $workstation->id ?? 'unknown',
                        'hashed_session_token' => $sessionHash
                    ]);

                    $departmentCount = $workstation->getUseraccount()->getDepartmentList()->count();

                    \App::$log->info('OIDC Login department check', [
                        'event' => 'oauth_login_department_check',
                        'timestamp' => date('c'),
                        'provider' => Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'username' => $username,
                        'department_count' => $departmentCount,
                        'has_departments' => ($departmentCount > 0),
                        'hashed_session_token' => $sessionHash
                    ]);

                    if (0 == $departmentCount) {
                        return \BO\Slim\Render::redirect(
                            'index',
                            [],
                            [
                                'oidclogin' => true
                            ]
                        );
                    }
                    return \BO\Slim\Render::redirect(
                        'workstationSelect',
                        [],
                        []
                    );
                } catch (\Exception $e) {
                    \App::$log->error('OIDC Login workstation error', [
                        'event' => 'oauth_login_workstation_error',
                        'timestamp' => date('c'),
                        'provider' => Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                    throw $e;
                }
            }

            \App::$log->error('OIDC Login invalid state', [
                'event' => 'oauth_login_invalid_state',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => 'zmsadmin'
            ]);

            throw new \BO\Slim\Exception\OAuthInvalid();
        } catch (\Exception $e) {
            \App::$log->error('OIDC Login error', [
                'event' => 'oauth_login_error',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => 'zmsadmin',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }
}
