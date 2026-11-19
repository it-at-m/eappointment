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

    #[\Override]
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
                    'response' => $this->readFixture("GET_workstation_appointment_admin.json")
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
        $exception->template = 'BO\Zmsbackend\Availability\Exception\AvailabilityNotFound';
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
                    'response' => $this->readFixture("GET_workstation_appointment_admin.json")
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
                    'response' => $this->readFixture("GET_workstation_appointment_admin.json")
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
                    'response' => $this->readFixture("GET_workstation_appointment_admin.json")
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

    public function testSlotBucketsAddUpOverlappingAvailabilities()
    {
        $availabilityData = [
            'type' => 'appointment',
            'weekday' => ['friday' => '32'],
            'repeat' => ['afterWeeks' => '1'],
            'workstationCount' => ['public' => '2', 'intern' => '3'],
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-01'),
            'endDate' => strtotime('2016-04-01'),
        ];
        $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList([
            new \BO\Zmsentities\Availability(array_merge($availabilityData, [
                'id' => '1',
                'startTime' => '08:00:00',
                'endTime' => '09:00:00',
            ])),
            // additional workstation inside availability 1
            new \BO\Zmsentities\Availability(array_merge($availabilityData, [
                'id' => '2',
                'startTime' => '08:15:00',
                'endTime' => '08:45:00',
                'workstationCount' => ['public' => '1', 'intern' => '1'],
            ])),
        ]);
        $buckets = ScopeAvailabilityDaySlotBuckets::readSlotBuckets(
            $availabilityList,
            new \BO\Zmsentities\Collection\ProcessList()
        );
        $this->assertSame(['08:00', '08:15', '08:30', '08:45'], array_keys($buckets));
        $this->assertEquals(3, $buckets['08:00']['intern']);
        $this->assertEquals(4, $buckets['08:15']['intern']);
        $this->assertEquals(3, $buckets['08:15']['public']);
        $this->assertEquals(4, $buckets['08:30']['intern']);
        $this->assertEquals(3, $buckets['08:45']['intern']);
    }
}

class ScopeAvailabilityDaySlotBuckets extends \BO\Zmsadmin\ScopeAvailabilityDay
{
    public static function readSlotBuckets($availabilityList, $processList): array
    {
        return static::getSlotBuckets($availabilityList, $processList);
    }
}
