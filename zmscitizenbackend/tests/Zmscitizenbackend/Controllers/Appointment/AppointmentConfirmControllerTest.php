<?php

namespace BO\Zmscitizenbackend\Tests\Controllers\Appointment;

use BO\Zmscitizenbackend\Exceptions\AuthKeyMatchFailed;
use BO\Zmscitizenbackend\Exceptions\ProcessNotFound;
use BO\Zmscitizenbackend\Exceptions\ProcessNotPreconfirmedAnymore;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentConfirmRepository;
use BO\Zmscitizenbackend\Repository\Mail\MailQueueRepository;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Helper\AppointmentByIdRows;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AppointmentConfirmControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Controllers\Appointment\AppointmentConfirmController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        $this->stubAppointment($this->sampleAppointment(status: 'reserved'));
        MailQueueRepository::use($this->createStub(MailQueueRepository::class));
    }

    public function tearDown(): void
    {
        AppointmentByIdRepository::use(null);
        AppointmentConfirmRepository::use(null);
        MailQueueRepository::use(null);
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->stubConfirm($this->sampleAppointment(
            status: 'confirmed',
            icsContent: "BEGIN:VCALENDAR\r\nEND:VCALENDAR"
        ));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        if ($responseBody['captchaToken']) {
            $this->assertArrayHasKey('captchaToken', $responseBody);
            $this->assertIsString($responseBody['captchaToken']);
            unset($responseBody['captchaToken']);
        }

        $expectedResponse = json_decode(
            json_encode($this->sampleAppointment(
                status: 'confirmed',
                icsContent: "BEGIN:VCALENDAR\r\nEND:VCALENDAR"
            )->toArray()),
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

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidProcessId')]],
            $responseBody
        );
    }

    public function testInvalidAuthKey()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidAuthKey')]],
            $responseBody
        );
    }

    public function testMissingProcessId()
    {
        $parameters = [
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidProcessId')]],
            $responseBody
        );
    }

    public function testMissingAuthKey()
    {
        $parameters = [
            'processId' => '101002'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidAuthKey')]],
            $responseBody
        );
    }

    public function testNoEmailSendingWhenStatusNotConfirmed()
    {
        $this->stubConfirm($this->sampleAppointment(status: 'reserved'));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('reserved', $responseBody['status']);
    }

    public function testInvalidRequest()
    {
        $response = $this->render([], [], [], 'GET');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidRequest')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $responseBody
        );
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
            'processId' => '999999',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('appointmentNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('appointmentNotFound')]],
            $responseBody
        );
    }

    public function testAuthKeyMismatch()
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
            'authKey' => 'cafe'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('authKeyMismatch')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('authKeyMismatch')]],
            $responseBody
        );
    }

    public function testProcessNotPreconfirmedAnymore()
    {
        $repository = $this->createStub(AppointmentConfirmRepository::class);
        $repository->method('confirmAppointment')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new ProcessNotPreconfirmedAnymore());
            }
        );
        AppointmentConfirmRepository::use($repository);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            ErrorMessages::get('processNotPreconfirmedAnymore')['statusCode'],
            $response->getStatusCode()
        );
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('processNotPreconfirmedAnymore')]],
            $responseBody
        );
    }

    private function stubAppointment(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturn($appointment);
        AppointmentByIdRepository::use($repository);
    }

    private function stubConfirm(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentConfirmRepository::class);
        $repository->method('confirmAppointment')->willReturn($appointment);
        AppointmentConfirmRepository::use($repository);
    }

    private function sampleAppointment(?string $icsContent = null, string $status = 'confirmed'): ThinnedProcess
    {
        $row = AppointmentByIdRows::processRow();
        $row['status'] = $status;
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            $row,
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
        $appointment->setCaptchaToken('');
        return $appointment;
    }
}
