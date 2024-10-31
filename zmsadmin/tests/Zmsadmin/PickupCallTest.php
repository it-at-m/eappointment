<?php

namespace BO\Zmsadmin\Tests;

class PickupCallTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "PickupCall";

    public function testRendering()
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
                    'response' => $this->readFixture("GET_process_82252_12a2_pending.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'response' => $this->readFixture("GET_process_82252_12a2_pickup.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Aufruf eines Abholers', (string)$response->getBody());
        $this->assertStringContainsString('H52452625 (Wartenummer 82252)', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWaitingnumber()
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
                    'url' => '/scope/141/queue/6/',
                    'response' => $this->readFixture("GET_process_spontankunde_pending.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'response' => $this->readFixture("GET_process_spontankunde_pickup.json")
                ]
            ]
        );
        $response = $this->render(['id' => 6], $this->parameters, []);
        $this->assertStringContainsString('Aufruf eines Abholers', (string)$response->getBody());
        $this->assertStringContainsString('(Wartenummer 100632)', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithoutName()
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
                    'url' => '/scope/141/queue/6/',
                    'response' => $this->readFixture("GET_process_spontankunde_empty_name.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'response' => $this->readFixture("GET_process_spontankunde_empty_name.json")
                ]
            ]
        );
        $response = $this->render(['id' => 6], $this->parameters, []);
        $this->assertStringContainsString('Aufruf eines Abholers', (string)$response->getBody());
        $this->assertStringContainsString(
            'Ist der Abholer mit der Wartenummer <strong>100632</strong> gekommen?',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotAllowedToEditProcess()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
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
                    'response' => $this->readFixture("GET_process_not_matching_id.json")
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Process\ProcessNotFound';

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
                    'url' => '/process/999999/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render(['id' => 999999], $this->parameters, []);
    }

    public function testAlreadyCalledProcess()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Workstation\WorkstationHasAssignedProcess';
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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2_pending.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
