<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Services\Core;

use BO\Zmscitizenbackend\Exceptions\SchemaValidation;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
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
                'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotFound',
                'appointmentNotFound',
            ],
            'AuthKeyMatchFailed' => [
                'BO\\Zmscitizenbackend\\Exceptions\\AuthKeyMatchFailed',
                'authKeyMismatch',
            ],
            'ExternalUserIdMatchFailed' => [
                'BO\\Zmscitizenbackend\\Exceptions\\ExternalUserIdMatchFailed',
                'authKeyMismatch',
            ],
            'ProcessNotReservedAnymore' => [
                'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotReservedAnymore',
                'processNotReservedAnymore',
            ],
            'ProcessNotPreconfirmedAnymore' => [
                'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotPreconfirmedAnymore',
                'processNotPreconfirmedAnymore',
            ],
            'ProcessDeleteFailed' => [
                'BO\\Zmscitizenbackend\\Exceptions\\ProcessDeleteFailed',
                'processDeleteFailed',
            ],
            'EmailRequired' => [
                'BO\\Zmscitizenbackend\\Exceptions\\EmailRequired',
                'emailIsRequired',
            ],
            'MoreThanAllowedAppointmentsPerMail' => [
                'BO\\Zmscitizenbackend\\Exceptions\\MoreThanAllowedAppointmentsPerMail',
                'tooManyAppointmentsWithSameMail',
            ],
            'AppointmentNotAvailable' => [
                'BO\\Zmscitizenbackend\\Exceptions\\AppointmentNotAvailable',
                'appointmentNotAvailable',
            ],
            'InvalidAvailabilityInput' => [
                'BO\\Zmscitizenbackend\\Exceptions\\InvalidAvailabilityInput',
                'invalidDateRange',
            ],
            'CalendarWithoutScopes' => [
                'BO\\Zmscitizenbackend\\Exceptions\\CalendarWithoutScopes',
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
