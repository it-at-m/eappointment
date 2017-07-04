<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessProcessingTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationProcessProcessing";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('H52452625 (Wartenr. 82252)', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
