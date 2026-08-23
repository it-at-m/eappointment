<?php

namespace BO\Zmscitizenbackend\Tests\Controllers\Appointment;

use BO\Zmscitizenbackend\Exceptions\AuthKeyMatchFailed;
use BO\Zmscitizenbackend\Exceptions\MoreThanAllowedAppointmentsPerMail;
use BO\Zmscitizenbackend\Exceptions\ProcessNotFound;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentUpdateRepository;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Helper\AppointmentByIdRows;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AppointmentUpdateControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Controllers\Appointment\AppointmentUpdateController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        $this->stubAppointment($this->sampleAppointment());
    }

    public function tearDown(): void
    {
        AppointmentByIdRepository::use(null);
        AppointmentUpdateRepository::use(null);
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->stubUpdate($this->updatedAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR"));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
            'customTextfield2' => "Another custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('captchaToken', $responseBody);
        $this->assertIsString($responseBody['captchaToken']);
        unset($responseBody['captchaToken']);

        $expectedResponse = json_decode(
            json_encode($this->updatedAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")->toArray()),
            true
        );
        unset($expectedResponse['captchaToken']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('icsContent', $responseBody);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $responseBody['icsContent']);
        unset($responseBody['icsContent']);
        unset($expectedResponse['icsContent']);
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => '',
            'customTextfield2' => 'Another custom text',
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testTooManyEmailsAtLocation()
    {
        $repository = $this->createStub(AppointmentUpdateRepository::class);
        $repository->method('updateClientData')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new MoreThanAllowedAppointmentsPerMail());
            }
        );
        AppointmentUpdateRepository::use($repository);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
            'customTextfield2' => "Another custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('tooManyAppointmentsWithSameMail')
            ]
        ];

        $this->assertEquals(
            ErrorMessages::get('tooManyAppointmentsWithSameMail')['statusCode'],
            $response->getStatusCode()
        );
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAppointmentNotFoundException()
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new ProcessNotFound());
            }
        );
        AppointmentByIdRepository::use($repository);

        $parameters = [
            'processId' => '101003',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
            'customTextfield2' => "Another custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('appointmentNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAuthKeyMismatchException()
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new AuthKeyMatchFailed());
            }
        );
        AppointmentByIdRepository::use($repository);

        $parameters = [
            'processId' => '101003',
            'authKey' => 'cafe',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
            'customTextfield2' => "Another custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('authKeyMismatch')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('authKeyMismatch')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    private function stubAppointment(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturn($appointment);
        AppointmentByIdRepository::use($repository);
    }

    private function stubUpdate(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentUpdateRepository::class);
        $repository->method('updateClientData')->willReturn($appointment);
        AppointmentUpdateRepository::use($repository);
    }

    private function sampleAppointment(?string $icsContent = null): ThinnedProcess
    {
        return (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
    }

    private function updatedAppointment(?string $icsContent = null): ThinnedProcess
    {
        $appointment = $this->sampleAppointment($icsContent);
        $appointment->familyName = 'TEST_USER';
        $appointment->email = 'test@muenchen.de';
        $appointment->telephone = '123456789';
        $appointment->customTextfield = 'Some custom text';
        $appointment->customTextfield2 = 'Another custom text';
        $appointment->setCaptchaToken('');
        return $appointment;
    }
}
