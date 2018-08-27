<?php

namespace BO\Zmsadmin\Tests;

class CounterAppointmentTimesTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "CounterAppointmentTimes";

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
                    'url' => '/scope/141/availability/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Terminzeiten', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
