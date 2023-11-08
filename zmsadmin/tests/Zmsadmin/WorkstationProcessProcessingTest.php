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
                    'parameters' => ['resolveReferences' => 2],
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
        $this->assertStringContainsString('H52452625', (string)$response->getBody());
        $this->assertStringContainsString('Wartenr. 82252', (string)$response->getBody());
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
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
