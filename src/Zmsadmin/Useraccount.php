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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if ($workstation->hasSuperUseraccount()) {
            $department = new \BO\Zmsentities\Department([
                'name' => 'Systemweite Nutzer'
                ]);
            $useraccountList = \App::$http->readGetResult(
                "/useraccount/",
                [
                    "resolveReferences" => 0,
                    "right" => "superuser",
                ]
            )
                ->getCollection()
                ;
            $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
            $organisationList = $ownerList->getOrganisationsByOwnerId(23);
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $department = $workstation->getUseraccount()->getDepartmentList()->getFirst();
            $departmentId = $department->id;
            $useraccountList = \App::$http->readGetResult("/department/$departmentId/useraccount/")->getCollection();
            $organisationList = new \BO\Zmsentities\Collection\OrganisationList();
            foreach ($workstation->useraccount->departments as $accountDepartment) {
                if (!$organisationList->getByDepartmentId($accountDepartment->id)->count()) {
                    $organisation = \App::$http->readGetResult(
                        "/department/" . $accountDepartment->id . "/organisation/",
                        array('resolveReferences'=>1)
                    )->getEntity();
                    $organisationList->addEntity($organisation);
                }
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'department' => $department,
                'useraccountList' => $useraccountList,
                'organisationList' => $organisationList,
            )
        );
    }
}
