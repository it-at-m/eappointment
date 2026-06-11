<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\RoleList;
use BO\Zmsentities\Collection\UseraccountList as Collection;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class UseraccountList extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (! $workstation->getUseraccount()->hasPermissions(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();

        $useraccountList = new Collection();
        if ($workstation->getUseraccount()->isSuperUser()) {
            $params = ["resolveReferences" => 0];
            if ($queryString !== null && $queryString !== '') {
                $params['query'] = $queryString;
            }
            $useraccountList = \App::$http->readGetResult("/useraccount/", $params)->getCollection();
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $departmentListIds = $departmentList->getIds();

            if (!empty($departmentListIds)) {
                $params = ['resolveReferences' => 0];
                if ($queryString !== null && $queryString !== '') {
                    $params['query'] = $queryString;
                }
                $useraccountList = \App::$http
                    ->readGetResult('/department/' . implode(',', $departmentListIds) . '/useraccount/', $params)
                    ->getCollection();
            }
        }

        $roleList = new RoleList();
        $roleMap = [];

        $roleResult = \App::$http->readGetResult('/roles/', []);
        if ($roleResult) {
            $loadedRoleList = $roleResult->getCollection();

            if ($loadedRoleList !== null) {
                $roleList = $loadedRoleList;

                foreach ($roleList as $role) {
                    $roleMap[$role->name] = $role->description ?: $role->name;
                }
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountList.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountList' => $useraccountList,
                'searchUserQuery' => $queryString,
                'ownerlist' => $ownerList,
                'success' => $success,
                'roleMap' => $roleMap,
                'roleList' => $roleList
            )
        );
    }
}
