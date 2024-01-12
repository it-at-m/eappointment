<?php

namespace BO\Zmsadmin\Tests;

class ProcessReserveTest extends Base
{
    protected $parameters = [
        'slotCount' => 1,
        'familyName' => 'Test BO',
        'telephone' => '1234567890',
        'email' => 'zmsbo@berlinonline.de',
        'scope' => 141,
        'requests' => [120703],
        'selecteddate' => '2016-04-01',
        'selectedtime' => '11-55'
    ];

    protected $classname = "ProcessReserve";

    public function testRendering()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_confirmed.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('Termin erfolgreich eingetragen', (string)$response->getBody());
        $this->assertStringContainsString('Die Vorgangsnummer für "Test BO" lautet: 100005', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithConflicts()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_confirmed.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_conflictlist_overbooked_slots.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString(
            'Es wurden Konflikte entdeckt',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Die Slots für diesen Zeitraum wurden überbucht',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReserveCopy()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('Termin erfolgreich eingetragen', (string)$response->getBody());
        $this->assertStringContainsString('Die Vorgangsnummer für "H52452625" lautet: 82252', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithConfirmations()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/194104/2b88/confirmation/mail/',
                    'response' => $this->readFixture("POST_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/194104/2b88/confirmation/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $parameters = array_merge(
            $this->parameters,
            array('sendConfirmation' => 1, 'sendMailConfirmation' => 1)
        );
        $response = $this->render($this->arguments, $parameters, [], 'POST');
        $this->assertStringContainsString('Termin erfolgreich eingetragen', (string)$response->getBody());
        $this->assertStringContainsString('Die Vorgangsnummer für "S4524" lautet: 194104', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithManualSlotCount()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 3, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_confirmed.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $parameters = array_merge($this->parameters, ['slotCount' => 3]);
        $response = $this->render($this->arguments, $parameters, [], 'POST');
        $this->assertStringContainsString('Termin erfolgreich eingetragen', (string)$response->getBody());
        $this->assertStringContainsString('Die Vorgangsnummer für "Test BO" lautet: 100005', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithRequiredMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141_required_mail.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'familyName' => 'Unittest',
            'reserve' => 1
        ], [], 'POST');
        $this->assertStringContainsString('den Standort muss eine', (string)$response->getBody());
        $this->assertStringContainsString('E-Mail Adresse eingetragen werden', (string)$response->getBody());
    }

    public function testValidationFailed()
    {

        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');
        
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'clientkey' => ''],
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_confirmed.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'familyName' => '',
            'telephone' => '1234567890',
            'email' => 'zmsbo@berlinonline.net',
            'scope' => 141,
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55'
        ], [], 'POST');
        $this->assertStringContainsString('Name eingegeben werden', (string)$response->getBody());
        $this->assertStringContainsString(
            'Es muss mindestens eine Dienstleistung ausgew\u00e4hlt werden!',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
