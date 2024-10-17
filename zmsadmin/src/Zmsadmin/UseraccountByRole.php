<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountByRole extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        $useraccountList = new Collection;
        if ($workstation->hasSuperUseraccount()) {

            try {
                $useraccountList = \App::$http->readGetResult("/role/$roleLevel/useraccount/")->getCollection();
            } catch (\Exception $e) {
                false;
            }

        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();

            foreach ($departmentList as $accountDepartment) {

                try {
                    $departmentUseraccountList = \App::$http
                        ->readGetResult("/role/$roleLevel/department/$accountDepartment->id/useraccount/")
                        ->getCollection();
                } catch (\Exception $e) {
                    continue;
                }

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
                'roleLevel' => $roleLevel,
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountListByRole' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}

