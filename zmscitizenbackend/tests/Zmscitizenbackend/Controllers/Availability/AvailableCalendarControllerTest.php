<?php

namespace BO\Zmscitizenbackend\Tests\Controllers\Availability;

use BO\Zmscitizenbackend\Exceptions\InvalidAvailabilityInput;
use BO\Zmscitizenbackend\Models\AvailableCalendar;
use BO\Zmscitizenbackend\Repository\Availability\AvailableCalendarHydrator;
use BO\Zmscitizenbackend\Repository\Availability\AvailableCalendarRepository;
use BO\Zmscitizenbackend\Repository\Office\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AvailableCalendarControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Controllers\Availability\AvailableCalendarController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        ValidationService::clearOfficeServicesCacheForTesting();
        $this->stubOfficeServices();
    }

    public function tearDown(): void
    {
        AvailableCalendarRepository::use(null);
        OfficesServicesRelationsRepository::use(null);
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->setCalendarAvailabilityApiCalls();

        $parameters = [
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'serviceCounts' => '1',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'slotsStartDate' => '2024-08-21',
            'slotsEndDate' => '2024-08-23',
            'prevBookableDate' => null,
            'nextBookableDate' => null,
            'availableDays' => [
                [
                    'date' => '2024-08-22',
                    'providerIDs' => '9999998',
                    'offices' => [
                        [
                            'officeId' => '9999998',
                            'appointments' => [32526616522],
                        ],
                    ],
                ],
                [
                    'date' => '2024-08-23',
                    'providerIDs' => '9999998',
                    'offices' => [
                        [
                            'officeId' => '9999998',
                            'appointments' => [32526616622, 32526616652],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testEmptyCalendar()
    {
        $this->setCalendarAvailabilityApiCalls('GET_calendar_availability_empty.json');

        $parameters = [
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'serviceCounts' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'slotsStartDate' => '2024-08-21',
            'slotsEndDate' => '2024-08-23',
            'prevBookableDate' => null,
            'nextBookableDate' => null,
            'availableDays' => [],
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testSlotsDateWindowPassedToRepository()
    {
        $repository = $this->createMock(AvailableCalendarRepository::class);
        $repository->expects($this->once())
            ->method('readAvailableCalendar')
            ->with(
                ['9999998'],
                ['1'],
                ['1'],
                '2024-08-21',
                '2024-10-21',
                '2024-08-21',
                '2024-09-21'
            )
            ->willReturn($this->calendarFromFixture());
        AvailableCalendarRepository::use($repository);

        $parameters = [
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'serviceCounts' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-10-21',
            'slotsStartDate' => '2024-08-21',
            'slotsEndDate' => '2024-09-21',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('2024-08-21', $responseBody['slotsStartDate']);
        $this->assertSame('2024-08-23', $responseBody['slotsEndDate']);
    }

    public function testInvalidSlotsDateFormat()
    {
        $parameters = [
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'serviceCounts' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'slotsStartDate' => 'not-a-date',
            'slotsEndDate' => 'also-bad',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidSlotsStartDate'),
                ErrorMessages::get('invalidSlotsEndDate'),
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidSlotsStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidDateFormat()
    {
        $parameters = [
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'serviceCounts' => '1',
            'startDate' => 'invalid-date',
            'endDate' => 'invalid-date',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testMissingStartDate()
    {
        $parameters = [
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingEndDate()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEndDate')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeId()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceId()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testEmptyServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidServiceCountFormat()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => 'one,two',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceCountExceedsMaximum()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '26',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAllParametersMissing()
    {
        $parameters = [];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate'),
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingStartDateAndEndDate()
    {
        $parameters = [
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndServiceId()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceIdAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingStartDateAndOfficeId()
    {
        $parameters = [
            'endDate' => '2024-09-04',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidOfficeId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingEndDateAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEndDate'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'serviceIds' => '1063424',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingStartDateEndDateAndOfficeId()
    {
        $parameters = [
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate'),
                ErrorMessages::get('invalidOfficeId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingStartDateEndDateAndServiceId()
    {
        $parameters = [
            'officeIds' => '102522',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate'),
                ErrorMessages::get('invalidServiceId')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingStartDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'endDate' => '2024-09-04',
            'serviceIds' => '1063424',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingEndDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'serviceIds' => '1063424',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEndDate'),
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testEmptyStartDateAndEndDate()
    {
        $parameters = [
            'startDate' => '',
            'endDate' => '',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidStartDate'),
                ErrorMessages::get('invalidEndDate')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidStartDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEndDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testNonNumericServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => '1063424',
            'serviceCounts' => 'abc,123',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ],
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidOfficeIdFormat()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => 'invalid',
            'serviceIds' => '1063424',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidServiceIdFormat()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '102522',
            'serviceIds' => 'invalid',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidDateRange()
    {
        $repository = $this->createStub(AvailableCalendarRepository::class);
        $repository->method('readAvailableCalendar')->willReturnCallback(
            static function (): AvailableCalendar {
                ExceptionService::handleException(new InvalidAvailabilityInput('startDate must not be after endDate'));
            }
        );
        AvailableCalendarRepository::use($repository);

        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeIds' => '9999998',
            'serviceIds' => '1',
            'serviceCounts' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDateRange')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDateRange')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    private function setCalendarAvailabilityApiCalls(string $fixture = 'GET_calendar_availability.json'): void
    {
        $repository = $this->createStub(AvailableCalendarRepository::class);
        $repository->method('readAvailableCalendar')->willReturn($this->calendarFromFixture($fixture));
        AvailableCalendarRepository::use($repository);
    }

    private function stubOfficeServices(): void
    {
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readServiceIdsByOfficeId')->willReturnCallback(
            static function (int $officeId): array {
                return match ($officeId) {
                    9999998 => ['1'],
                    102522 => ['1063424'],
                    default => [],
                };
            }
        );
        $repository->method('isCaptchaRequiredForOfficeIds')->willReturn(false);
        OfficesServicesRelationsRepository::use($repository);
    }

    private function calendarFromFixture(string $fixture = 'GET_calendar_availability.json'): AvailableCalendar
    {
        $body = json_decode($this->readFixture($fixture), true);
        $data = is_array($body['data'] ?? null) ? $body['data'] : [];

        return (new AvailableCalendarHydrator())->hydrate(
            $data,
            (string) ($data['startDate'] ?? ''),
            (string) ($data['endDate'] ?? '')
        );
    }
}
