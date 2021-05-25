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
                        'startDate' => '2016-04-01',
                        'endDate' => '2016-04-01'
                    ],
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Terminzeiten', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
