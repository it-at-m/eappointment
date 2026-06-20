<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Collection\UseraccountList;
use BO\Zmsentities\Collection\RoleList;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Exception\UserAccountAccessRightsFailed;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByRole extends BaseController
{
    private const SUPERUSER_ONLY_ROLES = [
        'system_admin',
        'audit_viewer',
    ];
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
        $roleName = $args['roleName'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (! $workstation->getUseraccount()->hasPermissions(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        if (
            ! $workstation->getUseraccount()->isSuperUser()
            && in_array($roleName, self::SUPERUSER_ONLY_ROLES, true)
        ) {
            throw new UserAccountAccessRightsFailed();
        }

        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        $useraccountList = new UseraccountList();
        if ($workstation->getUseraccount()->isSuperUser()) {
            $useraccountList = \App::$http
                ->readGetResult('/role/' . $roleName . '/useraccount/', ['resolveReferences' => 0])
                ->getCollection();
        } else {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
            $departmentList = $workstation->getUseraccount()->getDepartmentList();
            $departmentListIds = $departmentList->getIds();

            if (!empty($departmentListIds)) {
                $useraccountList = \App::$http
                    ->readGetResult(
                        '/role/' . $roleName . '/department/' . implode(',', $departmentListIds) . '/useraccount/',
                        ['resolveReferences' => 0]
                    )
                    ->getCollection();
            }
        }

        $roleList = new RoleList();
        $roleLabel = $roleName;

        $roleResult = \App::$http->readGetResult('/roles/', []);
        if ($roleResult && $roleResult->getCollection() !== null) {
            $roleList = $roleResult->getCollection();

            foreach ($roleList as $role) {
                if ($role->name === $roleName) {
                    $roleLabel = $role->description ?: $role->name;
                    break;
                }
            }
        }

        return Render::withHtml(
            $response,
            'page/useraccountList.twig',
            array(
                'title' => 'Nutzer',
                'roleLabel' => $roleLabel,
                'roleList' => $roleList,
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountListByRole' => $useraccountList,
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
