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

class UseraccountListByDepartments extends BaseController
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
        $workstation = (new Helper\User($request, 1))->checkRights('useraccount');
        $parameters = $request->getParams();

        $rawIds = array_map('trim', explode(',', $args['ids']));
        $rawIds = array_filter($rawIds, 'strlen');
        $requestedDepartmentIds = Helper\User::normalizeDepartmentIds($rawIds);

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

        $useraccountList = (new Useraccount())->readSearchByDepartmentIds($departmentIds, $parameters, 0, $workstation);

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }
}
