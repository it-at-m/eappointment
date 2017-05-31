<?php

namespace BO\Zmsapi\Tests;

class UseraccountUpdateTest extends Base
{
    protected $classname = "UseraccountUpdate";

    public function testRendering()
    {
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
              "id": "unittest"
            }'
        ], []);
        $this->assertContains('useraccount.json', (string)$response->getBody());
        $this->assertContains('unittest', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingLogin()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['loginname' => 'testadmin'], [], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
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
}
