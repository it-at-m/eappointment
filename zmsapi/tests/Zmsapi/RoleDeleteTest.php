<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsdb\Role as RoleRepository;
use BO\Zmsentities\Role as RoleEntity;

class RoleDeleteTest extends Base
{
    protected $classname = "RoleDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_delete',
            'description' => 'To delete',
            'permissions' => ['superuser'],
        ]));

        $response = $this->render(['id' => $created->id], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $this->expectException('\BO\Zmsapi\Exception\Role\RoleDoesNotExist');
        $this->expectExceptionCode(404);

        $this->render(['id' => 0], [], []);
    }

    /**
     * Replace the database user_role relation for a user by loginname with the specified role id.
     *
     * This bypasses Useraccount::writeUpdatedEntity() intentionally because these tests use
     * dynamic test roles
     */
    private function replaceUserRoleDirectly(\BO\Zmsdb\Useraccount $userQuery, string $loginName, int $roleId): void
    {
        $userQuery->perform(
            'DELETE FROM user_role
             WHERE user_id = (SELECT NutzerID FROM nutzer WHERE Name = ? LIMIT 1)',
            [$loginName]
        );

        $userQuery->perform(
            'INSERT INTO user_role (user_id, role_id)
             SELECT NutzerID, ? FROM nutzer WHERE Name = ?',
            [$roleId, $loginName]
        );
    }

    public function testDeleteAssignedRoleReturnsConflict()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $roleRepo = new RoleRepository();
        $userRepo = new \BO\Zmsdb\Useraccount();

        $createdRole = $roleRepo->addRole(new RoleEntity([
            'name' => 'test_role_api_delete_assigned',
            'description' => 'Assigned role',
            'permissions' => ['useraccount'],
        ]));
        $roleId = (int) $createdRole->id;

        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        $user->id = $user->id . rand();
        $createdUser = $userRepo->writeEntity($user);

        $this->replaceUserRoleDirectly($userRepo, $createdUser->id, $roleId);

        $this->expectException(\BO\Zmsapi\Exception\Role\RoleHasAssignedUsers::class);
        $this->expectExceptionCode(409);

        $this->render(['id' => $roleId], [], []);
    }
}
