<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

class Useraccount extends BaseController
{

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if ($workstation->hasSuperUseraccount()) {
            $department = new \BO\Zmsentities\Department([
                'name' => 'Systemweite Nutzer'
                ]);
            $userAccountList = \App::$http->readGetResult("/useraccount/")
                ->getCollection()
                ->withRights(['superuser']);
            $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>4))->getCollection();
            $organisationList = $ownerList->getOrganisationsByOwnerId(23);
        } else {
            $department = $workstation->getUseraccount()->getDepartmentList()->getFirst();
            $departmentId = $department->id;
            $userAccountList = \App::$http->readGetResult("/department/$departmentId/useraccount/")->getCollection();
            $organisation = \App::$http->readGetResult(
                "/department/$departmentId/organisation/",
                array('resolveReferences'=>1)
            )->getEntity();
            $organisationList = new \BO\Zmsentities\Collection\OrganisationList([$organisation]);
        }

        \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array (
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'department' => $department,
                'userAccountList' => $userAccountList,
                'organisationList' => $organisationList,
            )
        );
    }
}
