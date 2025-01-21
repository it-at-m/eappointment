<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

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
                'application' => 'zmsstatistic',
                'state_match' => ($state == $authKey)
            ]));
    
            if ($state == $authKey) {
                try {
                    $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
                    $username = $workstation->getUseraccount()->getLogin() . '@' . \BO\Zmsclient\Auth::getOidcProvider();
                    
                    // Log workstation access with username
                    error_log(json_encode([
                        'event' => 'oauth_workstation_access',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsstatistic',
                        'username' => $username,
                        'workstation_id' => $workstation->id ?? 'unknown'
                    ]));
    
                    $departmentCount = $workstation->getUseraccount()->getDepartmentList()->count();
                    
                    // Log department check with username
                    error_log(json_encode([
                        'event' => 'oauth_department_check',
                        'timestamp' => date('c'),
                        'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                        'application' => 'zmsstatistic',
                        'username' => $username,
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
                        'application' => 'zmsstatistic',
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
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic'
            ]));
            
            throw new \BO\Slim\Exception\OAuthInvalid();
            
        } catch (\Exception $e) {
            // Log any uncaught exceptions
            error_log(json_encode([
                'event' => 'oauth_error',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]));
            throw $e;
        }
    }
}
