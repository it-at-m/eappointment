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
            'serviceCount' => '1',
            'startDate' => '2024-08-21',
            'endDate' => '2024-08-23',
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

    public function testMissingParametersError()
    {
        $parameters = [];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseBody);
        $this->assertEquals('Missing or invalid parameters', $responseBody['error']);
        $this->assertArrayHasKey('availableDays', $responseBody);
        $this->assertEmpty($responseBody['availableDays']);
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
    
}
