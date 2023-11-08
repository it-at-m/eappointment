<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessCallTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessCall";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/82252/called/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingDirectCall()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/194104/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ]
            ]
        );
        $response = $this->render(['id' => 194104], ['direct' => 1], []);
        $this->assertRedirect($response, '/workstation/process/194104/called/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingPreCallRedirect()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/194104/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ]
            ]
        );
        $response = $this->render(['id' => 194104], $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/194104/precall/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
