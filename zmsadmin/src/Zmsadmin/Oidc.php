<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsclient\Auth;

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
            
            // Log state validation attempt
            error_log(json_encode([
                'event' => 'oauth_state_validation',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsadmin',
                'state_match' => ($state == $authKey)
            ]));

            if ($state == $authKey) {
                try {
                    $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
                    
                    // Log workstation access
                    error_log(json_encode([
                        'event' => 'oauth_workstation_access',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'workstation_id' => $workstation->id ?? 'unknown'
                    ]));

                    $departmentCount = $workstation->getUseraccount()->getDepartmentList()->count();
                    
                    // Log department check
                    error_log(json_encode([
                        'event' => 'oauth_department_check',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'department_count' => $departmentCount,
                        'has_departments' => ($departmentCount > 0)
                    ]));

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
                    // Log workstation access error
                    error_log(json_encode([
                        'event' => 'oauth_workstation_error',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsadmin',
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]));
                    throw $e;
                }
            }
            
            // Log invalid state
            error_log(json_encode([
                'event' => 'oauth_invalid_state',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider()
            ]));
            
            throw new \BO\Slim\Exception\OAuthInvalid();
            
        } catch (\Exception $e) {
            // Log any uncaught exceptions
            error_log(json_encode([
                'event' => 'oauth_error',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsadmin',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]));
            throw $e;
        }
    }
}
