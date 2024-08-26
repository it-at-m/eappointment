<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableAppointmentsListTest extends Base
{
    protected $classname = "AvailableAppointmentsList";

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
            'date' => '2024-08-29',
            'officeId' => '102522',
            'serviceId' => '1063424',
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

    public function testMissingParameters()
    {
        $parameters = [
            'date' => '2024-08-29',
            'serviceId' => '1063424',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertArrayHasKey('error', $responseBody);
        $this->assertEquals('Missing or invalid parameters', $responseBody['error']);

        $this->assertArrayHasKey('appointmentTimestamps', $responseBody);
        $this->assertEmpty($responseBody['appointmentTimestamps']);
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
            'date' => '2024-08-29',
            'officeId' => '102522',
            'serviceId' => '1063424',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertArrayHasKey('errorCode', $responseBody);
        $this->assertEquals('appointmentNotAvailable', $responseBody['errorCode']);

        $this->assertArrayHasKey('errorMessage', $responseBody);
        $this->assertEquals('Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar', $responseBody['errorMessage']);

        $this->assertArrayHasKey('appointmentTimestamps', $responseBody);
        $this->assertEmpty($responseBody['appointmentTimestamps']);
    }

}
