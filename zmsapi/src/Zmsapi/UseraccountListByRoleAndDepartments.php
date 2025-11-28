<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsdb\Useraccount;
use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByRoleAndDepartments extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $roleLevel = $args['level'];
        $workstation = (new Helper\User($request, 2))->checkRights('useraccount');

        $rawIds = array_map('trim', explode(',', $args['ids']));
        $rawIds = array_filter($rawIds, 'strlen');
        $requestedDepartmentIds = array_map('intval', $rawIds);

        $departmentIds = [];
        if ($workstation->getUseraccount()->isSuperUser()) {
            // Superusers can access all departments; no need to validate via DB here
            $departmentIds = $requestedDepartmentIds;
        } else {
            // Non-superusers must go through Helper\User::checkDepartments for access checks
            $departments = Helper\User::checkDepartments($requestedDepartmentIds);
            foreach ($departments as $department) {
                $departmentIds[] = $department->id;
            }
        }

        $useraccountList = (new Useraccount())->readListByRoleAndDepartmentIds($roleLevel, $departmentIds, 0, false, $workstation);

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}
