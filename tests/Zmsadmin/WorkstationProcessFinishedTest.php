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
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Kundendaten für Statistik', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingSave()
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
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'process' => [
                'id' => 157017,
                'clients' => [
                    [
                        'familyName' => 'M252',
                        'email' => 'zms@service.berlinonline.de'
                    ]
                ]
            ],
            'pickupScope' => 141,
            'statistic' => [
                'clientsCount' => 1,
                'ignoreRequests' => 1
            ]
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingStatisticDisabled()
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
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'process' => [
                'id' => 157017,
                'clients' => [
                    [
                        'familyName' => 'M252',
                        'email' => 'zms@service.berlinonline.de'
                    ]
                ]
            ],
            'pickupScope' => 141,
            'statistic' => [
                'clientsCount' => 1,
                'ignoreRequests' => 1
            ]
        ], []);
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
