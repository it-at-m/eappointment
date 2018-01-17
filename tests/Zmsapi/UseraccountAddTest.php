<?php

namespace BO\Zmsapi\Tests;

class UseraccountAddTest extends Base
{
    protected $classname = "UseraccountAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
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
              "lastLogin": 1459461600
            }'
        ], []);
        $this->assertContains('useraccount.json', (string)$response->getBody());
        $this->assertContains('unittest', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAlreadyExists()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountAlreadyExists');
        $this->expectExceptionCode(404);
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->render([], [
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

    public function testMissingLogin()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render([], [], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
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
