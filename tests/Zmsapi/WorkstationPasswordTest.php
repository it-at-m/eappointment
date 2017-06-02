<?php

namespace BO\Zmsapi\Tests;

class WorkstationPasswordTest extends Base
{
    protected $classname = "WorkstationPassword";

    public function testRendering()
    {
        $this->setWorkstation();
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
                "id": "testadmin",
                "password": "vorschau",
                "changePassword": ["testPassword","testPassword"]
            }'
        ], []);
        $this->assertContains('useraccount.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInvalidCredentials()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $response = $this->render([], [
            '__body' => '{
                "id": "testadmin",
                "password": "vorschau2"
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotValid()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->id = 'unittest';
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
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
                "id": "testadmin",
                "password": "vorschau",
                "changePassword": ["testPassword","testPassword2"]
            }'
        ], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->id = 'unittest';
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "password": "testPassword"
            }'
        ], []);
    }
}
