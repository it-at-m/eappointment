<?php

namespace BO\Zmsbackend\Tests\Useraccount\Api;

class UseraccountUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "UseraccountUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);

        $response = $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
                "email": "unittest@berlinonline.de",
                "id": "testadmin"
            }'
        ], []);

        $body = (string) $response->getBody();

        $this->assertStringContainsString('useraccount.json', $body);
        $this->assertStringContainsString('testadmin', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testChangePassword()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);

        $response = $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "roles": ["user_admin"],
                "departments": [
                    {"id": 74}
                ],
                "email": "unittest@berlinonline.de",
                "id": "unittest",
                "changePassword": ["newpassword", "newpassword"]
            }'
        ], []);

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        $this->assertTrue(password_verify('newpassword', $decoded['data']['password']));
        $this->assertStringContainsString('useraccount.json', $body);
        $this->assertStringContainsString('unittest', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'unittest'], [
            '__body' => '[]'
        ], []);
    }

    public function testInvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->expectException('BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidInput');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'testadmin'], [
            '__body' => '[]'
        ], []);
    }

    public function testAlreadyExists()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\UseraccountAlreadyExists');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'testuser'], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
                "id": "testadmin"
            }'
        ], []);
    }

    public function testSchemaUnvalid()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $response = $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "roles": ["agent_queue"],
                "departments": [
                    {"id": 74}
                ],
              "email": "unittest@berlinonline.de",
              "test": "unittest"
            }'
        ], []);
    }

    public function testSuperuserOnlyRoleRequiresSuperuser()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "roles": ["system_admin"],
                "departments": [
                    {"id": 74}
                ],
                "email": "unittest@berlinonline.de",
                "id": "unittest"
            }'
        ], []);
    }

    public function testInvalidRoleAssignment()
    {
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidRoleAssignment');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "roles": [],
                "departments": [
                    {"id": 74}
                ],
                "email": "unittest@berlinonline.de",
                "id": "unittest"
            }'
        ], []);
    }
}

