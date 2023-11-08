<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\UseraccountList as Collection;

class UseraccountByDepartment extends BaseController
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
        $departmentId = $args['id'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $department = \App::$http->readGetResult("/department/$departmentId/")->getEntity();
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
                'useraccountList' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'ownerlist' => $ownerList,
            )
        );
    }
}
