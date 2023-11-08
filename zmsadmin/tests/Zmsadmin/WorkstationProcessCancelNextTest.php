<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessCancelNextTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessCancelNext";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/next/?exclude=');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingWithProcess()
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
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/next/?exclude=');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingWithExcludeIds()
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
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['exclude' => 123456], []);
        $this->assertRedirect($response, '/workstation/process/next/?exclude=123456');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
