<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Useraccount extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        $useraccountList = new Collection();
        if ($workstation->hasSuperUseraccount()) {
            $useraccountList = \App::$http->readGetResult("/useraccount/", ["resolveReferences" => 0])->getCollection();
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            foreach ($departmentList as $accountDepartment) {
                $departmentUseraccountList = \App::$http
                    ->readGetResult("/department/$accountDepartment->id/useraccount/")
                    ->getCollection();
                if ($departmentUseraccountList) {
                    $useraccountList = $useraccountList->addList($departmentUseraccountList)->withoutDublicates();
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
                'useraccountList' => ($useraccountList) ?
                $useraccountList->sortByCustomStringKey('id') :
                new Collection(),
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
