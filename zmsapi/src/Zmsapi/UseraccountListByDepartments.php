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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $departments = Helper\User::checkDepartments(explode(',', $args['ids']));
        $parameters = $request->getParams();

        $departmentIds = [];
        foreach ($departments as $department) {
            $departmentIds[] = $department->id;
        }

        $useraccountList = (new Useraccount())->readSearchByDepartmentIds($departmentIds, $parameters, $resolveReferences, $workstation);

        // Add department entities if resolveReferences < 1 (same logic as UseraccountByDepartmentList)
        foreach ($useraccountList as $userAccount) {
            foreach ($departments as $department) {
                if ($resolveReferences < 1 && !$userAccount->getDepartmentById($department->getId())->hasId()) {
                    $userAccount->getDepartmentList()->addEntity($department);
                }
            }
        }

        $validUserAccounts = [];
        foreach ($useraccountList as $useraccount) {
            $validUserAccounts[] = $useraccount->withLessData();
        }
        $useraccountList = $validUserAccounts;

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }
}
