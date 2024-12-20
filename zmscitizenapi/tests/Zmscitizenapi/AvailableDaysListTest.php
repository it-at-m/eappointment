<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableDaysListTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AvailableDaysList";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'response' => $this->readFixture("GET_calendar.json")
                ]
            ]
        );
        $parameters = [
            'officeId' => '9999998',
            'serviceId' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
            'serviceCount' => '1',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'availableDays' => [
                "2024-08-21", "2024-08-22", "2024-08-23", "2024-08-26", "2024-08-27", "2024-08-28", "2024-08-29", "2024-08-30", 
                "2024-09-02", "2024-09-03", "2024-09-04", "2024-09-05", "2024-09-06", "2024-09-09", "2024-09-10", "2024-09-11", 
                "2024-09-12", "2024-09-13", "2024-09-16", "2024-09-17", "2024-09-18", "2024-09-19", "2024-09-20"
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testNoAvailableDays()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'response' => $this->readFixture("GET_calendar_empty_days.json")
                ]
            ]
        );

        $parameters = [
            'officeId' => '9999998',
            'serviceId' => '1',
            'serviceCount' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'noAppointmentForThisDay',
                    'errorMessage' => 'No available days found for the given criteria.',
                    'status' => 404,
                ]
            ],
            'status' => 404,
        ];
        $this->assertEquals(404, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorCode' => 'invalidStartDateFormat',
                    'errorMessage' => 'startDate must be in YYYY-MM-DD format.',
                ],
                [
                    'status' => 400,
                    'errorCode' => 'invalidEndDateFormat',
                    'errorMessage' => 'endDate must be in YYYY-MM-DD format.',
                ],
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());    
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceId',
                    'errorMessage' => 'serviceId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    

    public function testAllParametersMissing()
    {
        $parameters = [];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceId',
                    'errorMessage' => 'serviceId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceId',
                    'errorMessage' => 'serviceId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceId',
                    'errorMessage' => 'serviceId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    
    
    public function testMissingStartDateEndDateAndOfficeId()
    {
        $parameters = [
            'serviceId' => '1063424',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    
 
    public function testMissingStartDateEndDateAndServiceId()
    {
        $parameters = [
            'officeId' => '102522',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceId',
                    'errorMessage' => 'serviceId should be a 32-bit integer.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    

    public function testMissingStartDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'endDate' => '2024-09-04',
            'serviceId' => '1063424',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    
   
    public function testMissingEndDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'serviceId' => '1063424',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidOfficeId',
                    'errorMessage' => 'officeId should be a 32-bit integer.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidStartDate',
                    'errorMessage' => 'startDate is required and must be a valid date.',
                    'status' => 400,
                ],
                [
                    'errorCode' => 'invalidEndDate',
                    'errorMessage' => 'endDate is required and must be a valid date.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'invalidServiceCount',
                    'errorMessage' => 'serviceCounts should be an array of numeric values.',
                    'status' => 400,
                ]
            ],
            'status' => 400,
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }    
         
}
