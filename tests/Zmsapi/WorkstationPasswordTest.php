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
        $this->setWorkstation(138, static::$loginName, 143, static::$authKey);
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
        $this->setWorkstation(136, "testadmin");
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "password": "vorschau2",
                "email": "unittest@berlinonline.de",
                "departments": [
                    {"id":1}
                ]
            }'
        ], []);
    }

    public function testSchemsValidationFailed()
    {
        $this->setWorkstation(136, "testadmin");
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        //6 chars for password required
        $this->render([], [
            '__body' => '{
                "id": "'. static::$loginName .'",
                "password": "'. static::$authKey .'",
                "changePassword": ["test","test"],
                "email": "unittest@berlinonline.de"
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation(136, "testadmin");
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotValid()
    {
        $workstation = $this->setWorkstation(136, "testadmin");
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
                "email": "unittest@berlinonline.de",
                "changePassword": ["testPassword","testPassword2"]
            }'
        ], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation(136, "testadmin");
        $workstation->getUseraccount()->id = 'unittest';
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount_unknown.json')
        ], []);
    }
}
