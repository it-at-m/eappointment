<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ProcessPickupTest extends Base
{
    protected $classname = "ProcessPickup";

    const PROCESS_ID = 10030;
    const AUTHKEY = '1c56';
    const SCOPE_ID = 141;

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);

        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": '. self::SCOPE_ID . '
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
                            "id": '. self::SCOPE_ID . '
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
        $this->assertContains('Max Mustermann', (string)$response->getBody()); //department exists
    }

    public function testLogin()
    {
        $this->setExpectedException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render([], [
            '__body' => '{}',
        ], []);
    }


    public function testEmpty()
    {
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessInvalid');
        $this->render([], [
            '__body' => '{}',
        ], []);
    }

    public function testProcessNoAccess()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNoAccess');
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
            'scope' => new Scope([
                'id' => '133',
            ])
        ]);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 123
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
                            "id": '. self::SCOPE_ID . '
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testQueue()
    {
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);

        $response = $this->render([], [
            '__body' => '{
                "queue": {
                    "number": "55"
                }
            }'
        ], []);
        $this->assertContains('55', (string)$response->getBody()); //department exists
    }
}
