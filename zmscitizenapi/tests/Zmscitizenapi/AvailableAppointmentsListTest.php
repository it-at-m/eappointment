<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableAppointmentsListTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AvailableAppointmentsList";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments.json")
                ]
            ]
        );

        $parameters = [
            'date' => '2024-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('appointmentTimestamps', $responseBody);
        $this->assertNotEmpty($responseBody['appointmentTimestamps']);
        $this->assertTrue(is_array($responseBody['appointmentTimestamps']));
        $this->assertTrue(count($responseBody['appointmentTimestamps']) > 0);
        $this->assertTrue(is_numeric($responseBody['appointmentTimestamps'][0]));

        $this->assertArrayHasKey('lastModified', $responseBody);
        $this->assertTrue(is_numeric($responseBody['lastModified']));
    }

    public function testEmptyAppointments()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_empty.json")
                ]
            ]
        );

        $parameters = [
            'date' => '2024-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertArrayHasKey('errorCode', $responseBody);
        $this->assertEquals('appointmentNotAvailable', $responseBody['errorCode']);

        $this->assertArrayHasKey('errorMessage', $responseBody);
        $this->assertEquals('Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.', $responseBody['errorMessage']);

        $this->assertArrayHasKey('appointmentTimestamps', $responseBody);
        $this->assertEmpty($responseBody['appointmentTimestamps']);
    }

    public function testDateMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'date is required and must be a valid date', 'path' => 'date', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testOfficeIdMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'officeId should be a 32-bit integer', 'path' => 'officeId', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testServiceIdMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'officeId' => '10546',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceId should be a comma-separated string of integers', 'path' => 'serviceId', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testServiceCountMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceCount should be a comma-separated string of integers', 'path' => 'serviceCount', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testDateAndOfficeIdMissing()
    {
        $parameters = [
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'date is required and must be a valid date', 'path' => 'date', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'officeId should be a 32-bit integer', 'path' => 'officeId', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testDateAndServiceIdMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'date is required and must be a valid date', 'path' => 'date', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceId should be a comma-separated string of integers', 'path' => 'serviceId', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testDateAndServiceCountMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'date is required and must be a valid date', 'path' => 'date', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceCount should be a comma-separated string of integers', 'path' => 'serviceCount', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testOfficeIdAndServiceIdMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'officeId should be a 32-bit integer', 'path' => 'officeId', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceId should be a comma-separated string of integers', 'path' => 'serviceId', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testOfficeIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'officeId should be a 32-bit integer', 'path' => 'officeId', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceCount should be a comma-separated string of integers', 'path' => 'serviceCount', 'location' => 'body'],
            $responseBody['errors']
        );
    }

    public function testServiceIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '2024-09-21',
            'officeId' => '10546',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceId should be a comma-separated string of integers', 'path' => 'serviceId', 'location' => 'body'],
            $responseBody['errors']
        );
        $this->assertContains(
            ['type' => 'field', 'msg' => 'serviceCount should be a comma-separated string of integers', 'path' => 'serviceCount', 'location' => 'body'],
            $responseBody['errors']
        );
    }
}
