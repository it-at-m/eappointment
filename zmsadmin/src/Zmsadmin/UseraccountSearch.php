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













        /*if (!$workstation->hasSuperUseraccount()) {
            $useraccountList = $this->filteruseraccountListForUserRights($useraccountList, $privilegedScopeIds);
        }*/


        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountSearch.twig',
            array(
                'title' => 'User Search',
                'workstation' => $workstation,
                'useraccountList' => $collection,
                'searchUserQuery' => $queryString,
                'ownerlist' => $ownerList,
                'menuActive' => 'search'
            )
        );
    }

    /**
     * Filter user accounts based on user rights
     *
     * @param useraccountList|null $useraccountList
     * @param array $privilegedScopeIds
     * @return useraccountList
     */
    private function filteruseraccountListForUserRights(?useraccountList $useraccountList, array $privilegedScopeIds)
    {
        if (empty($useraccountList)) {
            return new useraccountList();
        }

        $filteredList = new useraccountList();

        foreach ($useraccountList as $useraccount) {
            if (isset($useraccount->rights['superuser']) && $useraccount->rights['superuser'] === "1") {
                continue;
            }

            if (isset($useraccount->departments) && (is_array($useraccount->departments) || is_object($useraccount->departments))) {
                foreach ($useraccount->departments as $department) {
                    if (isset($department->clusters) && (is_array($department->clusters) || is_object($department->clusters))) {
                        foreach ($department->clusters as $cluster) {
                            if (isset($cluster->scopes) && (is_array($cluster->scopes) || is_object($cluster->scopes))) {
                                foreach ($cluster->scopes as $scope) {
                                    if (in_array($scope->id, $privilegedScopeIds)) {
                                        $filteredList->addEntity(clone $useraccount);
                                        break 3;
                                    }
                                }
                            }
                        }
                    }

                    if (isset($department->scopes) && (is_array($department->scopes) || is_object($department->scopes))) {
                        foreach ($department->scopes as $scope) {
                            if (in_array($scope->id, $privilegedScopeIds)) {
                                $filteredList->addEntity(clone $useraccount);
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        return $filteredList;
    }






}
