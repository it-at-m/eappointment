<?php

namespace BO\Zmsadmin\Tests;

class ScopeAvailabilityMonthTest extends Base
{
    protected $arguments = [
        'id' => 141,
        'date' => '2016-04-01'
    ];

    protected $parameters = [];

    protected $classname = "ScopeAvailabilityMonth";

    public function testRendering()
    {
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  $startDate->modify('Last day of this month');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
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
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_calendar.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('availability-monthtable_calendar', (string)$response->getBody());
        $this->assertStringContainsString('data-availability-count="24"', (string)$response->getBody());
        $this->assertStringContainsString('daystatus--ticketprinter', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\SchemaValidation';

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }

    public function testRenderingAvailabilityFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  $startDate->modify('Last day of this month');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_calendar.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }


    public function testEmptyAvailabilityList()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound';
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  $startDate->modify('Last day of this month');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
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
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_calendar.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('availability-monthtable_calendar', (string)$response->getBody());
        $this->assertStringContainsString('data-availability-count="0"', (string)$response->getBody());
    }

    public function testAvailabilityListUnknownException()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';
        $startDate = new \DateTimeImmutable('2016-04-01');
        $endDate =  $startDate->modify('Last day of this month');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'exception' => $exception
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ],
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
