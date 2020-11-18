<?php

namespace BO\Zmsadmin\Tests;

class CounterAppointmentTimesTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selecteddate' => '2016-04-01'
    ];

    protected $classname = "CounterAppointmentTimes";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'resolveReferences' => 0,
                        'startDate' => '2016-04-01',
                        'endDate' => '2016-04-01',
                        'getOpeningTimes' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Terminzeiten', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
