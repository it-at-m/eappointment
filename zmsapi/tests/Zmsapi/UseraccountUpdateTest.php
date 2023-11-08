<?php

namespace BO\Zmsapi\Tests;

class UseraccountUpdateTest extends Base
{
    protected $classname = "UseraccountUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [
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
              "email": "unittest@berlinonline.de",
              "id": "unittest",
              "password": "unittest"
            }'
        ], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertStringContainsString('unittest', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testChangePassword()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [
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
              "email": "unittest@berlinonline.de",
              "id": "unittest",
              "changePassword": ["newpassword", "newpassword"]
            }'
        ], []);
        $this->assertTrue(password_verify(
            'newpassword',
            json_decode((string)$response->getBody(), 1)['data']['password']
        ));
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertStringContainsString('unittest', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
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
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'unittest'], [
            '__body' => '[]'
        ], []);
    }

    public function testInvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->expectException('BO\Zmsapi\Exception\Useraccount\UseraccountInvalidInput');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'testadmin'], [
            '__body' => '[]'
        ], []);
    }

    public function testAlreadyExists()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountAlreadyExists');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'testuser'], [
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
              "id": "testadmin"
            }'
        ], []);
    }

    public function testSchemaUnvalid()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $response = $this->render(['loginname' => 'testadmin'], [
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
              "email": "unittest@berlinonline.de",
              "test": "unittest"
            }'
        ], []);
    }

    public function testNoDepartments()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionMessage('Behördenauswahl');
        $this->expectExceptionCode(400);
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $response = $this->render(['loginname' => 'testadmin'], [
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
              "email": "unittest@berlinonline.de",
              "id": "unittest"
            }'
        ], []);
    }

    public function testMissingAssignedRights()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [
            '__body' => '{
                "rights": {
                "availability": "0",
                "basic": "0",
                "cluster": "0",
                "department": "0",
                "organisation": "1",
                "scope": "0",
                "sms": "0",
                "superuser": "0",
                "ticketprinter": "0",
                "useraccount": "1"
              },
              "departments": [
                  {"id": 74}
              ],
              "email": "unittest@berlinonline.de",
              "id": "unittest"
            }'
        ], []);
    }
}
