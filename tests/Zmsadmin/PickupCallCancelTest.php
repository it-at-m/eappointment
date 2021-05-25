<?php

namespace BO\Zmsadmin\Tests;

class PickupCallCancelTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "PickupCallCancel";

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
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'Der Abholer wurde erfolgreich aus der Bearbeitung entfernt und kann erneut aufgerufen werden.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
