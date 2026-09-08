<?php

namespace BO\Zmsbackend\Useraccount\Service;

use BO\Zmsbackend\Permission\Service\Permission as PermissionService;
use BO\Zmsbackend\Role\Service\Role as RoleService;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Useraccountprofile;

class Profile extends \BO\Zmsbackend\Base
{
    public function readEntity(Useraccount $useraccount): Useraccountprofile
    {
        $roleNames = $useraccount->getRoles();

        if (count($roleNames) !== 1) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidRoleAssignment();
        }

        $role = (new RoleService())->readRoleByName($roleNames[0], 0);

        if ($role === null) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidRoleAssignment();
        }

        $assignedPermissionNames = array_fill_keys(
            $role['permissions'] ?? [],
            true
        );

        $permissionDescriptions = [];

        foreach ((new PermissionService())->readAllPermissions() as $permission) {
            if (!isset($assignedPermissionNames[$permission->name])) {
                continue;
            }

            $permissionDescriptions[] = (string) (
                $permission->description ?: $permission->name
            );
        }

        $collator = new \Collator('de_DE');
        $collator->setStrength(\Collator::SECONDARY);

        usort(
            $permissionDescriptions,
            static function (string $descriptionA, string $descriptionB) use ($collator): int {
                return (int) $collator->compare(
                    $descriptionA,
                    $descriptionB
                );
            }
        );

        $username = preg_replace(
            '/@keycloak$/',
            '',
            (string) $useraccount->id
        );

        return new Useraccountprofile([
            'username' => (string) $username,
            'role' => (string) ($role->description ?: $role->name),
            'permissions' => $permissionDescriptions,
        ]);
    }
}
