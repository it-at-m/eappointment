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
            $collection = \App::$http->readGetResult("/useraccount/", ["resolveReferences" => 0])->getCollection();
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $collection = new \BO\Zmsentities\Collection\UseraccountList();
            foreach ($departmentList as $accountDepartment) {
                $useraccountList = \App::$http
                    ->readGetResult("/department/$accountDepartment->id/useraccount/")
                    ->getCollection();
                if ($useraccountList) {
                    $collection = $collection->addList($useraccountList)->withoutDublicates();
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
                'useraccountList' => $collection,
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
