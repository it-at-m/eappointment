<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Core\Service;

use BO\Zmscitizenbackend\Schema\Exception\SchemaValidation;
use BO\Zmscitizenbackend\Core\Service\ExceptionService;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use PHPUnit\Framework\TestCase;

class ExceptionServiceTest extends TestCase
{
    private function exceptionWithTemplate(
        string $template,
        string $message = 'Test message',
        int $code = 0
    ): \Exception {
        return new class ($message, $code, $template) extends \Exception {
            public string $template;

            public function __construct(string $message, int $code, string $template)
            {
                parent::__construct($message, $code);
                $this->template = $template;
            }
        };
    }
    /**
     * @dataProvider citizenExceptionProvider
     */
    public function testCitizenExceptions(string $template, string $errorKey): void
    {
        $exception = $this->exceptionWithTemplate($template);
        $expectedError = ErrorMessages::get($errorKey);

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                $expectedError['errorCode'] . ': ' . $expectedError['errorMessage'],
                $e->getMessage()
            );
            $this->assertEquals($expectedError['statusCode'], $e->getCode());
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function citizenExceptionProvider(): array
    {
        return [
            'ProcessNotFound' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotFound',
                'appointmentNotFound',
            ],
            'AuthKeyMatchFailed' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\AuthKeyMatchFailed',
                'authKeyMismatch',
            ],
            'ExternalUserIdMatchFailed' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\ExternalUserIdMatchFailed',
                'authKeyMismatch',
            ],
            'ProcessNotReservedAnymore' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotReservedAnymore',
                'processNotReservedAnymore',
            ],
            'ProcessNotPreconfirmedAnymore' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotPreconfirmedAnymore',
                'processNotPreconfirmedAnymore',
            ],
            'ProcessDeleteFailed' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessDeleteFailed',
                'processDeleteFailed',
            ],
            'EmailRequired' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\EmailRequired',
                'emailIsRequired',
            ],
            'MoreThanAllowedAppointmentsPerMail' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\MoreThanAllowedAppointmentsPerMail',
                'tooManyAppointmentsWithSameMail',
            ],
            'AppointmentNotAvailable' => [
                'BO\\Zmscitizenbackend\\Appointment\\Exception\\AppointmentNotAvailable',
                'appointmentNotAvailable',
            ],
            'InvalidAvailabilityInput' => [
                'BO\\Zmscitizenbackend\\Availability\\Exception\\InvalidAvailabilityInput',
                'invalidDateRange',
            ],
            'CalendarWithoutScopes' => [
                'BO\\Zmscitizenbackend\\Availability\\Exception\\CalendarWithoutScopes',
                'noAppointmentForThisScope',
            ],
        ];
    }

    public function testSchemaValidationMapsToInvalidSchema(): void
    {
        $exception = (new SchemaValidation())->setSchemaName('thinnedProcess');
        $expectedError = ErrorMessages::get('invalidSchema');

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                $expectedError['errorCode'] . ': ' . $expectedError['errorMessage'],
                $e->getMessage()
            );
            $this->assertEquals($expectedError['statusCode'], $e->getCode());
        }
    }

    public function testUnmappedException(): void
    {
        $exception = $this->exceptionWithTemplate('UnmappedException', 'Test message', 400);

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('UnmappedException: Test message', $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testNullTemplate(): void
    {
        $exception = new \Exception('Test message', 400);

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('unknown: Test message', $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testDefaultStatusCode(): void
    {
        $exception = new \Exception('Test message');

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(500, $e->getCode());
        }
    }

    public function testDroppedZmsbackendTemplateIsUnmapped(): void
    {
        $exception = $this->exceptionWithTemplate(
            'BO\\Zmsbackend\\Process\\Exception\\ProcessNotFound',
            'Test message',
            400
        );

        try {
            ExceptionService::handleException($exception);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                'BO\\Zmsbackend\\Process\\Exception\\ProcessNotFound: Test message',
                $e->getMessage()
            );
        }
    }
}
