<?php

namespace BO\Zmsadmin\Tests;

class ProcessSaveTest extends Base
{
    protected $arguments = [
        'id' => 82252,
    ];

    protected $parameters = [
        'slotCount' => 1,
        'familyName' => 'Test BO',
        'telephone' => '1234567890',
        'scope' => 141,
        'requests' => [120703]
    ];

    protected $classname = "ProcessSave";

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
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/',
                    'parameters' => [
                        'initiator' => null,
                        'slotType' => 'intern',
                        'slotsRequired' => 0
                    ],
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
        $this->assertStringNotContainsString('Es wurden Konflikte entdeckt', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithConflictOverbookedSlots()
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
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/',
                    'parameters' => [
                        'initiator' => null,
                        'slotType' => 'intern',
                        'slotsRequired' => 0
                    ],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
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
        $this->assertStringContainsString('Es wurden Konflikte entdeckt', (string)$response->getBody());
        $this->assertStringContainsString('08:10 - 08:20', (string)$response->getBody());
        $this->assertStringContainsString(
            'Die Slots für diesen Zeitraum wurden überbucht',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithConflictOutOfAvailability()
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
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/',
                    'parameters' => [
                        'initiator' => null,
                        'slotType' => 'intern',
                        'slotsRequired' => 0
                    ],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_conflictlist_out_of_availability.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('Es wurden Konflikte entdeckt', (string)$response->getBody());
        $this->assertStringContainsString('08:10 - 08:20', (string)$response->getBody());
        $this->assertStringContainsString(
            'Der Vorgang (12293716) befindet sich außerhalb der Öffnungszeit!',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithQueuedProcess()
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
                    'function' => 'readGetResult',
                    'url' => '/process/100011/',
                    'response' => $this->readFixture("GET_process_queued.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/',
                    'parameters' => [
                        'initiator' => null,
                        'slotType' => 'intern',
                        'slotsRequired' => 0
                    ],
                    'response' => $this->readFixture("GET_process_queued.json")
                ]
            ]
        );
        $response = $this->render(['id' => 100011], $this->parameters, [], 'POST');
        $this->assertStringContainsString(
            'Der Spontankunde mit der Wartenummer 5 wurde erfolgreich aktualisiert.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidationFailed()
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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
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
        $this->assertStringContainsString('Es muss mindestens eine Dienstleistung', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
