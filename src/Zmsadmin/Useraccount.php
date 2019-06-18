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
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        // API call to ownerlist is already restricted to user rights
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
        if ($workstation->hasSuperUseraccount()) {
            $useraccountList = \App::$http
                ->readGetResult(
                    "/useraccount/",
                    [
                        "resolveReferences" => 0,
                        "right" => "superuser",
                    ]
                )
                ->getCollection()
                ;
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $useraccountList = new \BO\Zmsentities\Collection\UseraccountList();
            foreach ($departmentList as $accountDepartment) {
                $accountUserList = \App::$http
                    ->readGetResult("/department/$accountDepartment->id/useraccount/")
                    ->getCollection();
                $useraccountList->addData($accountUserList);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountList' => $useraccountList,
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
