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
        // The shared fixtures predate the five-minute grid. Preserve the booked durations.
        $availabilityResponse = json_decode($this->readFixture('GET_scope_141_availability.json'), true);
        foreach ($availabilityResponse['data'] as &$availability) {
            $availability['slotTimeInMinutes'] = 5;
        }
        unset($availability);
        $processResponse = json_decode($this->readFixture('GET_processList_141_20160401.json'), true);
        foreach ($processResponse['data'] as &$process) {
            foreach ($process['appointments'] as &$appointment) {
                $appointment['slotCount'] = (int) $appointment['slotCount'] * 2;
                $appointment['availability']['slotTimeInMinutes'] = 5;
            }
            unset($appointment);
        }
        unset($process);

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
                    'response' => json_encode($availabilityResponse)
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/conflict/',
                    'parameters' => [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $startDate->format('Y-m-d')
                    ],
                    'response' => json_encode($processResponse)
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'response' => json_encode($processResponse)
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Öffnungszeiten für den Standort Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('data-busyslots="{&quot;68997&quot;:210}"', (string)$response->getBody());
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
        $this->assertStringContainsString('data-busyslots="{&quot;68997&quot;:0}"', (string)$response->getBody());
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
            'slotTimeInMinutes' => 5,
            'startDate' => strtotime('2016-04-01'),
            'endDate' => strtotime('2016-04-01'),
        ];
        $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList([
            new \BO\Zmsentities\Availability(array_merge($availabilityData, [
                'id' => '1',
                'startTime' => '08:00:00',
                'endTime' => '08:20:00',
            ])),
            // additional workstation inside availability 1
            new \BO\Zmsentities\Availability(array_merge($availabilityData, [
                'id' => '2',
                'startTime' => '08:05:00',
                'endTime' => '08:15:00',
                'workstationCount' => ['public' => '1', 'intern' => '1'],
            ])),
        ]);
        $buckets = ScopeAvailabilityDaySlotBuckets::readSlotBuckets(
            $availabilityList,
            new \BO\Zmsentities\Collection\ProcessList()
        );
        $this->assertSame(['08:00', '08:05', '08:10', '08:15'], array_keys($buckets));
        $this->assertEquals(3, $buckets['08:00']['intern']);
        $this->assertEquals(4, $buckets['08:05']['intern']);
        $this->assertEquals(3, $buckets['08:05']['public']);
        $this->assertEquals(4, $buckets['08:10']['intern']);
        $this->assertEquals(3, $buckets['08:15']['intern']);
    }

    public function testSlotBucketsUseTheBookingGridAcrossAnAvailabilityBoundary()
    {
        $availabilityData = [
            'weekday' => ['friday' => '32'],
            'repeat' => ['afterWeeks' => '1'],
            'workstationCount' => ['public' => '1', 'intern' => '1'],
            'slotTimeInMinutes' => 5,
            'startDate' => strtotime('2016-04-01'),
            'endDate' => strtotime('2016-04-01'),
        ];
        $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        foreach ([['09:00:00', '09:15:00'], ['09:15:00', '09:40:00']] as $id => [$start, $end]) {
            $availabilityList->addEntity(new \BO\Zmsentities\Availability($availabilityData + [
                'id' => $id + 1,
                'startTime' => $start,
                'endTime' => $end,
            ]));
        }
        $processList = new \BO\Zmsentities\Collection\ProcessList([
            new \BO\Zmsentities\Process(['appointments' => [[
                'date' => strtotime('2016-04-01 09:00'),
                'slotCount' => 7,
            ]]]),
        ]);

        $buckets = ScopeAvailabilityDaySlotBuckets::readSlotBuckets($availabilityList, $processList);
        $this->assertSame(1, $buckets['09:00']['occupiedCount']);
        $this->assertSame(1, $buckets['09:15']['occupiedCount']);
        $this->assertSame(1, $buckets['09:30']['occupiedCount']);
        $this->assertSame(0, $buckets['09:35']['occupiedCount']);
    }

    public function testSlotBucketsShowOffGridBookings()
    {
        $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList([
            new \BO\Zmsentities\Availability([
                'id' => 1,
                'weekday' => ['friday' => '32'],
                'workstationCount' => ['intern' => 1],
                'startDate' => strtotime('2016-04-01'),
                'endDate' => strtotime('2016-04-01'),
                'startTime' => '09:00:00',
                'endTime' => '09:20:00',
                'slotTimeInMinutes' => 5,
            ]),
        ]);
        $processList = new \BO\Zmsentities\Collection\ProcessList([
            new \BO\Zmsentities\Process(['appointments' => [[
                'date' => strtotime('2016-04-01 09:02'),
                'slotCount' => 2,
            ]]]),
        ]);

        $buckets = ScopeAvailabilityDaySlotBuckets::readSlotBuckets($availabilityList, $processList);
        $this->assertSame([0, 1, 1, 0], array_values(array_column($buckets, 'occupiedCount')));
        $this->assertCount(1, $processList->withOutAvailability($availabilityList));
    }
}

class ScopeAvailabilityDaySlotBuckets extends \BO\Zmsadmin\ScopeAvailabilityDay
{
    public static function readSlotBuckets($availabilityList, $processList): array
    {
        $usage = $processList->getAvailabilitySlotUsage($availabilityList);
        return static::getSlotBuckets($availabilityList, $usage['occupiedByTime']);
    }
}
