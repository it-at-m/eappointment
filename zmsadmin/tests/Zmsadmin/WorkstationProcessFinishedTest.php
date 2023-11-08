<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessFinishedTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationProcessFinished";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Kundendaten für Statistik', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingAssignedProcess()
    {
        $this->expectException('BO\Zmsentities\Exception\WorkstationMissingAssignedProcess');
        $this->expectExceptionCode(404);
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_without_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }

    public function testRenderingSaveWithIgnoreRequests()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'process' => [
                'id' => 157017,
                'clients' => [
                    [
                        'familyName' => 'M252',
                        'email' => 'test@example.com'
                    ]
                ]
            ],
            'pickupScope' => 141,
            'statistic' => [
                'clientsCount' => 1
            ],
            'ignoreRequests' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSaveWithNoRequestsPerformed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'process' => [
                'id' => 157017,
                'clients' => [
                    [
                        'familyName' => 'M252',
                        'email' => 'test@example.com'
                    ]
                ]
            ],
            'pickupScope' => 141,
            'statistic' => [
                'clientsCount' => 1
            ],
            'noRequestsPerformed' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSaveWithRequestCountList()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'process' => [
                'id' => 157017,
                'clients' => [
                    [
                        'familyName' => 'M252',
                        'email' => 'test@example.com'
                    ]
                ]
            ],
            'pickupScope' => 141,
            'statistic' => [
                'clientsCount' => 1
            ],
            'requestCountList' => ['120335' => 1]
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingStatisticDisabledWithPickup()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_statistic_disabled_default_pickup.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('Kundendaten für Statistik', (string)$response->getBody());
        $this->assertStringNotContainsString('Bearbeitete Dienstleistungen des Kunden', (string)$response->getBody());
        $this->assertStringContainsString('ignoreRequests', (string)$response->getBody());
        $this->assertStringContainsString('selected="selected"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingStatisticDisabledWithoutPickup()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_statistic_disabled.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingStatisticEnabledWithoutPickup()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_statistic_enabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('Kundendaten für Statistik', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
