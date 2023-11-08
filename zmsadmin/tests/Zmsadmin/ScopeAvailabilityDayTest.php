<?php

namespace BO\Zmsadmin\Tests;

class ScopeAvailabilityDayTest extends Base
{
    protected $arguments = [
        'id' => 141,
        'date' => '2016-04-01'
    ];

    protected $parameters = [];

    protected $classname = "ScopeAvailabilityDay";

    public function testRendering()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
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
                        'endDate' => $startDate->format('Y-m-d')
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
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('data-busyslots="{&quot;68997&quot;:105}"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound';
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
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
                    'exception' => $exception
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
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('data-busyslots="[]"', (string)$response->getBody());
    }

    public function testEmptyProcessList()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
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
                        'endDate' => $startDate->format('Y-m-d')
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'response' => $this->readFixture("GET_processList_fake_entry.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringNotContainsString('data-busyslots="{&quot;68997&quot;:105}"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUnknownException()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  new \DateTimeImmutable('2016-04-01');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
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
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
