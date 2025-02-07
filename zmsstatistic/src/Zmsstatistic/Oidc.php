<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

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
            $authKey = \BO\Zmsclient\Auth::getKey();
            
            \App::$log->info('OIDC state validation', [
                'event' => 'oauth_state_validation',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic',
                'state_match' => ($state == $authKey)
            ]);
    
            if ($state == $authKey) {
                try {
                    $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
                    $username = $workstation->getUseraccount()->id . '@' . \BO\Zmsclient\Auth::getOidcProvider();
                    
                    \App::$log->info('OIDC workstation access', [
                        'event' => 'oauth_workstation_access',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsstatistic',
                        'username' => $username,
                        'workstation_id' => $workstation->id ?? 'unknown'
                    ]);
    
                    $departmentCount = $workstation->getUseraccount()->getDepartmentList()->count();
                    
                    // Log department check with username
                    \App::$log->info('OIDC department check', [
                        'event' => 'oauth_department_check',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsstatistic',
                        'username' => $username,
                        'department_count' => $departmentCount,
                        'has_departments' => ($departmentCount > 0)
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
                  \App::$log->error('OIDC workstation error', [
                        'event' => 'oauth_workstation_error',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsstatistic',
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                    throw $e;
                }
            }
            
            \App::$log->error('OIDC invalid state', [
                'event' => 'oauth_invalid_state',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic'
            ]);
            
            throw new \BO\Slim\Exception\OAuthInvalid();
            
        } catch (\Exception $e) {
            \App::$log->error('OIDC error', [
                'event' => 'oauth_error',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }
}
