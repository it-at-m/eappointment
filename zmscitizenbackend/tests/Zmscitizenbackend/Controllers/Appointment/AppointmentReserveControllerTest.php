<?php

namespace BO\Zmscitizenbackend\Tests\Controllers\Appointment;

use BO\Zmscitizenbackend\Exceptions\AppointmentNotAvailable;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Repository\AppointmentReserveRepository;
use BO\Zmscitizenbackend\Repository\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Helper\AppointmentByIdRows;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AppointmentReserveControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Controllers\Appointment\AppointmentReserveController";

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
        AppointmentReserveRepository::use(null);
        OfficesServicesRelationsRepository::use(null);
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->stubReserve($this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR"));

        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('captchaToken', $responseBody);
        $this->assertIsString($responseBody['captchaToken']);
        unset($responseBody['captchaToken']);

        $expectedResponse = json_decode(
            json_encode($this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")->toArray()),
            true
        );
        unset($expectedResponse['captchaToken']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('icsContent', $responseBody);
        if ($responseBody['icsContent'] !== null) {
            $this->assertStringContainsString('BEGIN:VCALENDAR', $responseBody['icsContent']);
        }
        unset($responseBody['icsContent']);
        unset($expectedResponse['icsContent']);
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAppointmentNotAvailable()
    {
        $repository = $this->createStub(AppointmentReserveRepository::class);
        $repository->method('reserveAppointment')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new AppointmentNotAvailable());
            }
        );
        AppointmentReserveRepository::use($repository);

        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => "32526616300"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotAvailable')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('appointmentNotAvailable')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeId()
    {
        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceId()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingTimestamp()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [1]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndServiceId()
    {
        $parameters = [
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndTimestamp()
    {
        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [1]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceIdAndTimestamp()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [1]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingAllFields()
    {
        $parameters = [];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidOfficeIdFormat()
    {
        $parameters = [
            'officeId' => 'invalid_id',
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
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
            'officeId' => 10546,
            'serviceId' => ['invalid_service_id'],
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidTimestampFormat()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => 'invalid_timestamp'
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    public function testEmptyServiceIdArray()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => [],
            'serviceCount' => [1],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidServiceCount()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => ['invalid'],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    private function stubOfficeServices(): void
    {
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readServiceIdsByOfficeId')->willReturnCallback(
            static function (int $officeId): array {
                return $officeId === 10546 ? ['1063423'] : [];
            }
        );
        $repository->method('isCaptchaRequiredForOfficeIds')->willReturn(false);
        OfficesServicesRelationsRepository::use($repository);
    }

    private function stubReserve(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentReserveRepository::class);
        $repository->method('reserveAppointment')->willReturn($appointment);
        AppointmentReserveRepository::use($repository);
    }

    private function sampleAppointment(?string $icsContent = null): ThinnedProcess
    {
        $row = AppointmentByIdRows::processRow();
        $row['status'] = 'reserved';
        $row['id'] = 10546;
        $row['name'] = 'Gewerbeamt (KVR-III/21)';
        $row['display_name'] = 'Gewerbeamt';
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            $row,
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
        $appointment->setCaptchaToken('');
        $appointment->officeId = 10546;
        return $appointment;
    }
}

