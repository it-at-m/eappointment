<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableDaysListTest extends Base
{
    protected $classname = "AvailableDaysList";

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

        $this->assertStringContainsString(
            '2024-08-21',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            '2024-08-22',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            '2024-08-23',
            (string)$response->getBody()
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoAvailableDays()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'response' => $this->readFixture("GET_calendar_empty_days.json") // Simulate a response with no available days
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

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('errorCode', $responseBody);
        $this->assertEquals('noAppointmentForThisScope', $responseBody['errorCode']);
        $this->assertArrayHasKey('errorMessage', $responseBody);
        $this->assertEquals('No available days found for the given criteria', $responseBody['errorMessage']);
        $this->assertArrayHasKey('availableDays', $responseBody);
        $this->assertEmpty($responseBody['availableDays']);
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
    
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('errorCode', $responseBody);
        $this->assertArrayHasKey('errorMessage', $responseBody);
        $this->assertEquals('An diesem Standort gibt es aktuell leider keine freien Termine', $responseBody['errorMessage']);
        $this->assertArrayHasKey('availableDays', $responseBody);
        $this->assertEmpty($responseBody['availableDays']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('serviceId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][0]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][0]['msg']);
    }

    public function testAllParametersMissing()
    {
        $parameters = [];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(5, $responseBody['errors']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
        $this->assertEquals('serviceId should be a 32-bit integer', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('serviceId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][1]['msg']);
    }
    
    
    public function testMissingStartDateEndDateAndOfficeId()
    {
        $parameters = [
            'serviceId' => '1063424',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(3, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][1]['msg']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][2]['msg']);
    }
 
    public function testMissingStartDateEndDateAndServiceId()
    {
        $parameters = [
            'officeId' => '102522',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(3, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][1]['msg']);
        $this->assertEquals('serviceId should be a 32-bit integer', $responseBody['errors'][2]['msg']);
    }

    public function testMissingStartDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'endDate' => '2024-09-04',
            'serviceId' => '1063424',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(3, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][1]['msg']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][2]['msg']);
    }
   
    public function testMissingEndDateOfficeIdAndServiceCount()
    {
        $parameters = [
            'startDate' => '2024-08-29',
            'serviceId' => '1063424',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(3, $responseBody['errors']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('officeId should be a 32-bit integer', $responseBody['errors'][1]['msg']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][2]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(2, $responseBody['errors']);
        $this->assertEquals('startDate is required and must be a valid date', $responseBody['errors'][0]['msg']);
        $this->assertEquals('endDate is required and must be a valid date', $responseBody['errors'][1]['msg']);
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
    
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertCount(1, $responseBody['errors']);
        $this->assertEquals('serviceCount should be a comma-separated string of integers', $responseBody['errors'][0]['msg']);
    }
         
}
