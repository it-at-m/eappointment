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
        if ($request->getParam("state") == \BO\Zmsclient\Auth::getKey()) {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            if (0 == $workstation->getUseraccount()->getDepartmentList()->count()) {
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
        }
    }
}
