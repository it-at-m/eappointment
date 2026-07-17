<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Availability;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Prophecy\Argument;

class AvailableCalendarByOfficeControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Availability\AvailableCalendarByOfficeController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        ValidationService::clearOfficeServicesCacheForTesting();
    }

    public function testRendering()
    {
        $this->setCalendarAvailabilityApiCalls();

        $parameters = [
            'officeId' => '9999998',
            'serviceId' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'serviceCount' => '1',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'availableDays' => [
                [
                    'time' => '2024-08-22',
                    'providerIDs' => '9999998',
                    'offices' => [
                        [
                            'officeId' => '9999998',
                            'appointments' => [32526616522],
                        ],
                    ],
                ],
                [
                    'time' => '2024-08-23',
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
            'officeId' => '9999998',
            'serviceId' => '1',
            'serviceCount' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'availableDays' => [],
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidDateFormat()
    {
        $parameters = [
            'officeId' => '9999998',
            'serviceId' => '1',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => 'one,two',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '26',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'serviceCount' => '1',
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
            'officeId' => '102522',
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
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
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
            'serviceId' => '1063424',
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
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceCount' => '1',
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
            'serviceId' => '1063424',
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
            'serviceId' => '1063424',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => 'abc,123',
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
            'officeId' => 'invalid',
            'serviceId' => '1063424',
            'serviceCount' => '1',
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
            'officeId' => '102522',
            'serviceId' => 'invalid',
            'serviceCount' => '1',
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
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsbackend\\Calendar\\Exception\\InvalidFirstDay';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture('GET_SourceGet_dldb.json'),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/calendar/availability/',
                'parameters' => Argument::that(static function ($params): bool {
                    return is_array($params)
                        && ($params['startDate'] ?? null) === '2024-08-29'
                        && ($params['endDate'] ?? null) === '2024-09-04'
                        && ($params['officeId'] ?? null) === '9999998'
                        && ($params['serviceId'] ?? null) === '1'
                        && ($params['serviceCount'] ?? null) === '1';
                }),
                'exception' => $exception,
            ],
        ]);

        $parameters = [
            'startDate' => '2024-08-29',
            'endDate' => '2024-09-04',
            'officeId' => '9999998',
            'serviceId' => '1',
            'serviceCount' => '1',
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
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture('GET_SourceGet_dldb.json'),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/calendar/availability/',
                'parameters' => Argument::that(static function ($params): bool {
                    return is_array($params)
                        && ($params['startDate'] ?? null) === '2024-08-21'
                        && ($params['endDate'] ?? null) === '2024-08-23'
                        && ($params['officeId'] ?? null) === '9999998'
                        && ($params['serviceId'] ?? null) === '1'
                        && ($params['serviceCount'] ?? null) === '1';
                }),
                'response' => $this->readFixture($fixture),
            ],
        ]);
    }
}
