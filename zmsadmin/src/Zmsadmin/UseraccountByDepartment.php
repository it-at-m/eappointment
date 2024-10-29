<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountByDepartment extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $departmentId = $args['id'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $department = \App::$http->readGetResult("/department/$departmentId/")->getEntity();

        $useraccountList = new Collection;
        $useraccountList = \App::$http->readGetResult("/department/$departmentId/useraccount/")->getCollection();
        $workstationList = \App::$http->readGetResult("/department/$departmentId/workstation/")->getCollection();

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'department' => $department,
                'workstationList' => $workstationList,
                'useraccountListByDepartment' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
