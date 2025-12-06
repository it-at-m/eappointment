<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByRole extends BaseController
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
        $roleLevel = $args['level'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        $useraccountList = new Collection();
        if ($workstation->hasSuperUseraccount()) {
            $useraccountList = \App::$http->readGetResult("/role/$roleLevel/useraccount/")->getCollection();
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $departmentListIds = $departmentList->getIds();

            if (!empty($departmentListIds)) {
                $departmentUseraccountList = \App::$http
                    ->readGetResult(
                        "/role/$roleLevel/department/" . implode(',', $departmentListIds) . "/useraccount/",
                        ['resolveReferences' => 0]
                    )
                    ->getCollection();
                if ($departmentUseraccountList) {
                    $useraccountList = $useraccountList->addList($departmentUseraccountList)->withoutDublicates();
                }
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountList.twig',
            array(
                'title' => 'Nutzer',
                'roleLevel' => $roleLevel,
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountListByRole' => $useraccountList,
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
