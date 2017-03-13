<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsclient\Auth;

class LogoutBySuperuser extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        \App::$http->readDeleteResult('/workstation/'. $workstation->useraccount['id'] .'/')->getEntity();

        return \BO\Slim\Render::redirect(
            'useraccountByDepartment',
            array(),
            array()
        );
    }
}
