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
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Aufruf eines Abholers', (string)$response->getBody());
        $this->assertContains('H52452625 (Wartenr. 82252)', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotAllowedToEditProcess()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed';
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
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/pickup/',
                    'response' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
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
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
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
