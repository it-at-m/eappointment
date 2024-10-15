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
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();

        if ($workstation->hasSuperUseraccount()) {

            $collection = \App::$http->readGetResult('/useraccount/search/', [
                'query' => $queryString,
                'resolveReferences' => 1,
            ])->getCollection();

        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $collection = new \BO\Zmsentities\Collection\UseraccountList();
            foreach ($departmentList as $accountDepartment) {
                $useraccountList = \App::$http
                    ->readGetResult("/department/$accountDepartment->id/useraccount/search/", [
                        'query' => $queryString,
                        'resolveReferences' => 1
                    ])
                    ->getCollection();
                if ($useraccountList) {
                    $collection = $collection->addList($useraccountList)->withoutDublicates();
                }
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountSearch.twig',
            array(
                'title' => 'User Search',
                'workstation' => $workstation,
                'useraccountList' => $collection,
                'searchUserQuery' => $queryString,
                'ownerlist' => $ownerList,
                'menuActive' => 'useraccount'
            )
        );
    }

}
