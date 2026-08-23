<?php

namespace BO\Zmscitizenbackend\Tests\Appointment\Controller;

use BO\Zmscitizenbackend\Appointment\Exception\AuthKeyMatchFailed;
use BO\Zmscitizenbackend\Appointment\Exception\ProcessNotFound;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Core\Service\ExceptionService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Appointment\Helper\AppointmentByIdRows;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AppointmentByIdControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Appointment\Controller\AppointmentByIdController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        putenv('ALTCHA_CAPTCHA_SITE_KEY=FAKE_SITE_KEY');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE=https://captcha-k.muenchen.de/api/v1/captcha/challenge');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_VERIFY=https://captcha-k.muenchen.de/api/v1/captcha/verify');
        putenv('CAPTCHA_ENABLED=1');
        putenv('CAPTCHA_TOKEN_SECRET=FAKE_TOKEN_SECRET_THAT_IS_SUFFICIENTLY_LONG');

        \App::initialize();
    }

    public function tearDown(): void
    {
        AppointmentByIdRepository::use(null);
        putenv('ALTCHA_CAPTCHA_SITE_KEY=');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_VERIFY=');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE=');
        putenv('CAPTCHA_ENABLED=');
        putenv('CAPTCHA_TOKEN_SECRET=');

        parent::tearDown();
    }

    public function testRendering()
    {
        $this->stubAppointment($this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR"));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);

        if ($responseBody['captchaToken']) {
            $this->assertArrayHasKey('captchaToken', $responseBody);
            $this->assertIsString($responseBody['captchaToken']);
            unset($responseBody['captchaToken']);
        }

        $expectedResponse = json_decode(
            json_encode($this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")->toArray()),
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

    public function testMissingProcessId()
    {
        $parameters = [
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingAuthKey()
    {
        $parameters = [
            'processId' => '101002',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => 'invalid',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidAuthKey()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 12345,
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testBothParametersMissing()
    {
        $parameters = [];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAppointmentNotFound()
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new ProcessNotFound());
            }
        );
        AppointmentByIdRepository::use($repository);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('appointmentNotFound');
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotFound')
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
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
            'processId' => '101002',
            'authKey' => 'cafe',
        ];

        $response = $this->render([], $parameters, []);
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

    private function sampleAppointment(?string $icsContent = null): ThinnedProcess
    {
        return (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
    }
}
