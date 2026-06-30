<?php

namespace BO\Zmsapi\Tests;

class UseraccountAddTest extends Base
{
    protected $classname = "UseraccountAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $response = $this->render([], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest",
                "password": "unittest",
              "email": "unittest@berlinonline.de",
              "lastLogin": 1459461600
            }'
        ], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertStringContainsString('unittest', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAlreadyExists()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountAlreadyExists');
        $this->expectExceptionCode(404);
        $this->setWorkstation(137, "testadmin")->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->render([], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
                "id": "testuser",
                "password": "unittest"
            }'
        ], []);
    }

    public function testSuperuserOnlyRoleRequiresSuperuser()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->setWorkstation(137, "testadmin")->getUseraccount()->setPermissions('useraccount');
        $this->render([], [
            '__body' => '{
                "roles": ["system_admin"],
                "departments": [
                    {"id": 74}
                ],
              "id": "unittest-rights-failed",
              "password": "unittest"
            }'
        ], []);
    }

    public function testInvalidUserName()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->setWorkstation(137, "testadmin")->getUseraccount()->setPermissions('useraccount');
        $this->render([], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
              "id": "äas#d wrong user name",
              "password": "unittest"
            }'
        ], []);
    }

    public function testSuperuserCanAddSystemAdminRole()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->setDepartment(74);
        $response = $this->render([], [
            '__body' => '{
                "roles": ["system_admin"],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest-superuser",
                "email": "test@zms.de",
              "password": "unittest"
            }'
        ], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertStringContainsString('unittest-superuser', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testSchemaUnvalid()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->render([], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest",
              "email": "unittest@berlinonline.de",
                "lastLogin": "invalid-timestamp",
              "test": "unittest",
              "password": "unittest"
            }'
        ], []);
    }

    public function testInvalidRoleAssignment()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountInvalidRoleAssignment');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "roles": [],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest-invalid-role",
                "password": "unittest"
            }'
        ], []);
    }

    public function testMissingLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render([], [], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testInvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->expectException('BO\Zmsapi\Exception\Useraccount\UseraccountInvalidInput');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '[]'
        ], []);
    }

    public function testMultipleRolesRejected()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);

        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountInvalidRoleAssignment');
        $this->expectExceptionCode(400);

        $this->render([], [
            '__body' => '{
                "roles": ["agent_queue", "appointment_admin"],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest-multiple-roles",
                "password": "unittest"
            }'
        ], []);
    }

    public function testMissingRolesRejected()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);

        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountInvalidRoleAssignment');
        $this->expectExceptionCode(400);

        $this->render([], [
            '__body' => '{
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest-missing-roles",
                "password": "unittest"
            }'
        ], []);
    }


    public function testUnknownRoleRejected()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);

        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountInvalidRoleAssignment');
        $this->expectExceptionCode(400);

        $this->render([], [
            '__body' => '{
                "roles": ["__unknown_role__"],
                "departments": [
                    {"id": 74}
                ],
                "id": "unittest-unknown-role",
                "password": "unittest"
            }'
        ], []);
    }
}
