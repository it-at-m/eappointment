<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsbackend\Useraccount\Service\Useraccount;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByDepartments extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('useraccount');
        $parameters = $request->getParams();

        $rawIds = array_map('trim', explode(',', $args['ids']));
        $rawIds = array_filter($rawIds, 'strlen');
        $requestedDepartmentIds = \BO\Zmsbackend\Helper\User::normalizeDepartmentIds($rawIds);

        $departmentIds = [];
        if ($workstation->getUseraccount()->isSuperUser()) {
            // Superusers can access all departments; no need to validate via DB here
            $departmentIds = $requestedDepartmentIds;
        } else {
            // Non-superusers must go through \BO\Zmsbackend\Helper\User::checkDepartments for access checks
            $departments = \BO\Zmsbackend\Helper\User::checkDepartments($requestedDepartmentIds);
            foreach ($departments as $department) {
                $departmentIds[] = $department->id;
            }
        }

        $useraccountList = (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readSearchByDepartmentIds($departmentIds, $parameters, 0, $workstation);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }
}
