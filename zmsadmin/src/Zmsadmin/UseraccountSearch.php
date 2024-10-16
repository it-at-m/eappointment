<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class UseraccountSearch extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();

        $useraccountList = new \BO\Zmsentities\Collection\UseraccountList();
        if ($workstation->hasSuperUseraccount()) {

            $useraccountList = \App::$http->readGetResult('/useraccount/search/', [
                'query' => $queryString,
                'resolveReferences' => 1,
            ])->getCollection();

        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();

            foreach ($departmentList as $accountDepartment) {
                $departmentUseraccountList = \App::$http
                    ->readGetResult("/department/$accountDepartment->id/useraccount/search/", [
                        'query' => $queryString,
                        'resolveReferences' => 1
                    ])
                    ->getCollection();
                if ($departmentUseraccountList) {
                    $useraccountList = $useraccountList->addList($departmentUseraccountList)->withoutDublicates();
                }
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountSearch.twig',
            array(
                'title' => 'User Search',
                'workstation' => $workstation,
                'useraccountList' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'searchUserQuery' => $queryString,
                'ownerlist' => $ownerList,
                'menuActive' => 'useraccount',
                'success' => $success,
            )
        );
    }

}
