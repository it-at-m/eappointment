<?php

namespace BO\Zmsapi\Tests;

class UseraccountAddTest extends Base
{
    protected $classname = "UseraccountAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "0",
                "cluster": "0",
                "department": "0",
                "organisation": "0",
                "scope": "0",
                "sms": "0",
                "superuser": "0",
                "ticketprinter": "0",
                "useraccount": "1"
              },
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
        $this->setWorkstation(137, "testadmin")->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "1",
                "cluster": "0",
                "department": "0",
                "organisation": "0",
                "scope": "0",
                "sms": "0",
                "superuser": "0",
                "ticketprinter": "0",
                "useraccount": "0"
              },
              "departments": [
                  {"id": 74}
              ],
              "id": "testuser",
              "password": "unittest"
            }'
        ], []);
    }

    public function testRightsFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountAccessRightsFailed');
        $this->expectExceptionCode(403);
        $this->setWorkstation(137, "testadmin")->getUseraccount()->setRights('useraccount');
        $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "1",
                "cluster": "0",
                "department": "0",
                "organisation": "0",
                "scope": "0",
                "sms": "0",
                "superuser": "1",
                "ticketprinter": "0",
                "useraccount": "0"
              },
              "departments": [
                  {"id": 74}
              ],
              "id": "unittest_rights_failed",
              "password": "unittest"
            }'
        ], []);
    }

    public function testSuperuserAddRights()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->setDepartment(74);
        $response = $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "1",
                "basic": "1",
                "cluster": "1",
                "department": "1",
                "organisation": "1",
                "scope": "1",
                "sms": "1",
                "superuser": "1",
                "ticketprinter": "1",
                "useraccount": "1"
              },
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
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "0",
                "cluster": "0",
                "department": "0",
                "organisation": "0",
                "scope": "0",
                "sms": "0",
                "superuser": "0",
                "ticketprinter": "0",
                "useraccount": "1"
              },
              "departments": [
                  {"id": 74}
              ],
              "id": "unittest",
              "email": "unittest@berlinonline.de",
              "lastLogin": 1459461600,
              "test": "unittest",
              "password": "unittest"
            }'
        ], []);
    }

    public function testNoDepartments()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionMessage('Behördenauswahl');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->render([], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "0",
                "cluster": "0",
                "department": "0",
                "organisation": "0",
                "scope": "0",
                "sms": "0",
                "superuser": "0",
                "ticketprinter": "0",
                "useraccount": "1"
              },
              "id": "unittest",
              "email": "unittest@berlinonline.de",
              "lastLogin": 1459461600,
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
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testInvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->expectException('BO\Zmsapi\Exception\Useraccount\UseraccountInvalidInput');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '[]'
        ], []);
    }
}
