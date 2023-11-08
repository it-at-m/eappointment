<?php

namespace BO\Zmsadmin\Tests;

class ScopeAvailabilityDayConflictsTest extends Base
{
    protected $arguments = [
        'id' => 141,
        'date' => '2016-04-01'
    ];

    protected $parameters = [];

    protected $classname = "ScopeAvailabilityDayConflicts";

    public function testRendering()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 3
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertStringContainsString('conflicts', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
