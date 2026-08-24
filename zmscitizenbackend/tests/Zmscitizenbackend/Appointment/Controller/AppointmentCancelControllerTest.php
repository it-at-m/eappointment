<?php

namespace BO\Zmscitizenbackend\Tests\Appointment\Controller;

use BO\Zmscitizenbackend\Appointment\Exception\AuthKeyMatchFailed;
use BO\Zmscitizenbackend\Appointment\Exception\ProcessDeleteFailed;
use BO\Zmscitizenbackend\Appointment\Exception\ProcessNotFound;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentCancelRepository;
use BO\Zmscitizenbackend\Mail\Repository\MailQueueRepository;
use BO\Zmscitizenbackend\Core\Service\ExceptionService;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Appointment\Helper\AppointmentByIdRows;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class AppointmentCancelControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Appointment\Controller\AppointmentCancelController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        $this->stubAppointment($this->sampleAppointment());
        MailQueueRepository::use($this->createStub(MailQueueRepository::class));
    }

    public function tearDown(): void
    {
        AppointmentByIdRepository::use(null);
        AppointmentCancelRepository::use(null);
        MailQueueRepository::use(null);
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->stubCancel($this->sampleAppointment(status: 'deleted'));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        unset($responseBody['captchaToken']);

        $expectedResponse = json_decode(
            json_encode($this->sampleAppointment(status: 'deleted')->toArray()),
            true
        );
        unset($expectedResponse['captchaToken']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('icsContent', $responseBody);
        $this->assertNull($responseBody['icsContent']);
        unset($responseBody['icsContent']);
        unset($expectedResponse['icsContent']);
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testPastAppointment()
    {
        $this->stubAppointment($this->sampleAppointment(
            timestamp: (string) \App::$now->modify('-1 hour')->getTimestamp()
        ));

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('appointmentCanNotBeCanceled')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('appointmentCanNotBeCanceled')]],
            $responseBody
        );
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

    public function testNoEmailSendingWhenReserved()
    {
        $this->stubAppointment($this->sampleAppointment(status: 'reserved'));
        $this->stubCancel($this->sampleAppointment(status: 'reserved'));

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

    public function testProcessDeleteFailed()
    {
        $repository = $this->createStub(AppointmentCancelRepository::class);
        $repository->method('cancelAppointment')->willReturnCallback(
            static function (): ThinnedProcess {
                ExceptionService::handleException(new ProcessDeleteFailed());
            }
        );
        AppointmentCancelRepository::use($repository);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('processDeleteFailed')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('processDeleteFailed')]],
            $responseBody
        );
    }

    private function stubAppointment(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentByIdRepository::class);
        $repository->method('readAppointmentById')->willReturn($appointment);
        AppointmentByIdRepository::use($repository);
    }

    private function stubCancel(ThinnedProcess $appointment): void
    {
        $repository = $this->createStub(AppointmentCancelRepository::class);
        $repository->method('cancelAppointment')->willReturn($appointment);
        AppointmentCancelRepository::use($repository);
    }

    private function sampleAppointment(
        ?string $icsContent = null,
        string $status = 'confirmed',
        ?string $timestamp = null
    ): ThinnedProcess {
        $row = AppointmentByIdRows::processRow();
        $row['status'] = $status;
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            $row,
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
        $appointment->timestamp = $timestamp
            ?? (string) \App::$now->modify('+1 day')->getTimestamp();
        $appointment->setCaptchaToken('');
        return $appointment;
    }
}
