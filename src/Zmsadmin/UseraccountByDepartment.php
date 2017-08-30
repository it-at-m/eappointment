<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        $department = $workstation->getUseraccount()->getDepartment($departmentId);
        $userAccountList = \App::$http->readGetResult("/department/$departmentId/useraccount/")->getCollection();
        $workstationList = \App::$http->readGetResult("/department/$departmentId/workstation/")->getCollection();

        $organisation = \App::$http->readGetResult(
            "/department/$departmentId/organisation/",
            array('resolveReferences'=>1)
        )->getEntity();
        $organisationList = new \BO\Zmsentities\Collection\OrganisationList([$organisation]);

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'department' => $department,
                'workstationList' => $workstationList,
                'userAccountList' => $userAccountList->sortByCustomStringKey('id'),
                'organisationList' => $organisationList,
            )
        );
    }
}
