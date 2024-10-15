<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Useraccount;

class UseraccountByRoleList extends BaseController
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
        $roleLevel = $args['id'];
        $validator = $request->getAttribute('validator');
        (new Helper\User($request, 2))->checkRights('useraccount');
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        
        $useraccountList = (new Useraccount)->readListRole($roleLevel, $resolveReferences);
        if (! $useraccountList or count($useraccountList) === 0) {
            throw new Exception\Useraccount\UserRoleNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $useraccountList;
        
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);

        return $response;
    }

    /*protected function getUseraccountListWithAccess($useraccountList)
    {
        $collection = new Collection();
        foreach ($useraccountList as $useraccount) {
            if ($useraccount->isSuperUser() || $this->hasSystemWideAccess($useraccount)) {
                $collection->addEntity(clone $useraccount);
            }
        }
        return $collection;
    } */

    /*protected function hasSystemWideAccess($useraccount)
    {
        $assignedDepartments = (new Useraccount())->readAssignedDepartmentList($useraccount);
        return (0 === $assignedDepartments->count());
    }*/
}
