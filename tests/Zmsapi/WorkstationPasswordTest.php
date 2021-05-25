<?php

namespace BO\Zmsapi\Tests;

class WorkstationPasswordTest extends Base
{
    protected $classname = "WorkstationPassword";

    public static $loginName = 'superuser';

    public static $authKey = 'vorschau';

    public function __construct()
    {
        parent::__construct();
        static::$loginName = (! \App::DEBUG) ? static::$loginName : 'testadmin';
        static::$authKey = (! \App::DEBUG) ? static::$authKey : 'vorschau';
    }

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
                "id": "'. static::$loginName .'",
                "password": "'. static::$authKey .'",
                "email": "unittest@berlinonline.de",
                "changePassword": ["testPassword","testPassword"],
                "departments": [
                    {"id":1}
                ]
            }'
        ], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInvalidCredentials()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $this->render([], [
            '__body' => '{
                "id": "'. static::$loginName .'",
                "password": "vorschau2",
                "departments": [
                    {"id":1}
                ]
            }'
        ], []);
    }

    public function testSchemsValidationFailed()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        //6 chars for password required
        $this->render([], [
            '__body' => '{
                "id": "'. static::$loginName .'",
                "password": "vorschau2"
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
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
                "id": "'. static::$loginName .'",
                "password": "'. static::$authKey .'",
                "changePassword": ["testPassword","testPassword2"]
            }'
        ], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->id = 'unittest';
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount_unknown.json')
        ], []);
    }
}
