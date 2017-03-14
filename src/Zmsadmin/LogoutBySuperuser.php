<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsclient\Auth;

use \BO\Mellon\Validator;

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
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $workstationToLogout = $validator->getParameter('workstation')->isArray()->getValue();

        if (array_key_exists('useraccount', $workstationToLogout) && isset($workstationToLogout['useraccount']['id'])) {
            $userAccount = \App::$http
                ->readGetResult('/useraccount/'. $workstationToLogout['useraccount']['id'] .'/')->getEntity();
            $workstation->getUseraccount()->hasEditAccess($userAccount);
            \App::$http->readDeleteResult('/workstation/'. $workstationToLogout['useraccount']['id'] .'/');
        }

        $departmentId = Validator::value($args['id'])->isNumber()->getValue();
        return \BO\Slim\Render::redirect(
            'useraccountByDepartment',
            array('id' => $departmentId),
            array()
        );
    }
}
