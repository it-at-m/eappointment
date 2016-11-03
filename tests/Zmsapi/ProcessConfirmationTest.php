<?php

namespace BO\Zmsapi\Tests;

class ProcessConfirmationTest extends Base
{
    protected $classname = "ProcessConfirmationNotification";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
        $this->assertContains('Otto-Suhr-Allee 100', (string)$response->getBody()); //department exists
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testProcessNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $response = $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "1234",
                "scope": {
                    "id": 141
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }
}
