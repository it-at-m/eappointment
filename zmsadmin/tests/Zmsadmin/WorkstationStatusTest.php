<?php

namespace BO\Zmsadmin\Tests;

class WorkstationStatusTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationStatus";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
