<?php

namespace BO\Zmsapi\Tests;

class ProcessConfirmationMailTest extends Base
{
    protected $classname = "ProcessConfirmationMail";

    const PROCESS_ID = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => $this->readFixture('GetProcess_10029.json')
        ], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        return $response;
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141,
                    "provider": {
                        "id": 123456,
                        "name": "Flughafen Schönefeld, Aufsicht",
                        "source": "dldb"
                    },
                    "shortName": "Zentrale"
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
                            "id": 141,
                            "provider": {
                                "id": 123456,
                                "name": "Flughafen Schönefeld, Aufsicht",
                                "source": "dldb"
                            },
                            "shortName": "Zentrale"
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
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "1234",
                "scope": {
                    "id": 141,
                    "provider": {
                        "id": 123456,
                        "name": "Flughafen Schönefeld, Aufsicht",
                        "source": "dldb"
                    },
                    "shortName": "Zentrale"
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
                            "id": 141,
                            "provider": {
                                "id": 123456,
                                "name": "Flughafen Schönefeld, Aufsicht",
                                "source": "dldb"
                            },
                            "shortName": "Zentrale"
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testMissingMail()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\EmailRequired');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141,
                    "provider": {
                        "id": 123456,
                        "name": "Flughafen Schönefeld, Aufsicht",
                        "source": "dldb"
                    },
                    "shortName": "Zentrale",
                    "preferences": {
                        "client": {
            				"emailRequired": 1
            			}
                    }
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141,
                            "provider": {
                                "id": 123456,
                                "name": "Flughafen Schönefeld, Aufsicht",
                                "source": "dldb"
                            },
                            "shortName": "Zentrale"
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testUnvalidInput()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "unvalid"
            }'
        ], []);
    }
}
