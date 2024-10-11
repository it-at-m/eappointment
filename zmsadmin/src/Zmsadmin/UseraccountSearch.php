<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\UseraccountList;

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
        $departmentId = $args['id'] ?? null;
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();
        if ($departmentId) {
            $userAccountList = \App::$http->readGetResult("/department/$departmentId/useraccount/search/", [
                'query' => $queryString,
                'resolveReferences' => 1,
            ])->getCollection();
        } else {
            $userAccountList = \App::$http->readGetResult('/useraccount/search/', [
                'query' => $queryString,
                'resolveReferences' => 1,
            ])->getCollection();
        }
        
        $scopeIds = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList()->getIds();
        if (!$workstation->hasSuperUseraccount()) {
            $userAccountList = $this->filterUserAccountListForUserRights($userAccountList, $scopeIds);
        }
        
        return \BO\Slim\Render::withHtml(
            $response,
            'page/userAccountSearch.twig',
            array(
                'title' => 'User Search',
                'workstation' => $workstation,
                'userAccountList' => $userAccountList,
                'searchUserQuery' => $queryString,
                'menuActive' => 'search'
            )
        );
    }

    /**
     * Filter user accounts based on user rights
     *
     * @param UseraccountList|null $userAccountList
     * @param array $scopeIds
     * @return UseraccountList
     */
    private function filterUserAccountListForUserRights(?UseraccountList $userAccountList, array $scopeIds)
    {
        if (empty($userAccountList)) {
            return new UseraccountList();
        }

        $filteredList = new UseraccountList();

        foreach ($userAccountList as $userAccount) {
            if (in_array($userAccount->scope->id, $scopeIds)) {
                $filteredList->addEntity(clone $userAccount);
            }
        }

        return $filteredList;
    }
}
