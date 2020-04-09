<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount;

class DepartmentUseraccountList extends BaseController
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
        $workstation = (new Helper\User($request, 2))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = Helper\User::checkDepartment($args['id']);
        
        $useraccountList = (new Useraccount)->readCollectionByDepartmentId($department->id, $resolveReferences);
        $useraccountList = $this->getListByWorkstation($useraccountList, $workstation);
        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected function getListByWorkstation($useraccountList, $workstation)
    {
        $collection = new \BO\Zmsentities\Collection\UseraccountList();
        $departmentList = $workstation->getDepartmentList();
        foreach ($useraccountList as $useraccount) {
            $accessedList = $departmentList->withAccess($useraccount);
            if ($accessedList->count()) {
                $collection->addEntity(clone $useraccount);
            }
        }
        return $collection;
    }
}
