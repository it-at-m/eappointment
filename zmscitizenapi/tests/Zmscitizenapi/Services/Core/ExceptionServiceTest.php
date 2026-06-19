<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\ExceptionService;
use PHPUnit\Framework\TestCase;

class MockException extends \Exception
{
    public string $template;

    public function __construct(string $message = '', int $code = 0, string $template = '')
    {
        parent::__construct($message, $code);
        $this->template = $template;
    }
}

class ExceptionServiceTest extends TestCase
{
    /**
     * @dataProvider processExceptionProvider
     */
    public function testProcessExceptions(string $template, string $errorKey): void
    {
        $exception = new MockException('Test message', 0, $template);
        $expectedError = ErrorMessages::get($errorKey);
        
        try {
            ExceptionService::handleException($exception, 'testMethod');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                $expectedError['errorCode'] . ': ' . $expectedError['errorMessage'],
                $e->getMessage()
            );
            $this->assertEquals($expectedError['statusCode'], $e->getCode());
        }
    }

    public static function processExceptionProvider(): array
    {
        return [
            'ProcessNotFound' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessNotFound',
                'appointmentNotFound'
            ],
            'AuthKeyMatchFailed' => [
                'BO\\Zmsbackend\\Process\\Exception\\AuthKeyMatchFailed',
                'authKeyMismatch'
            ],
            'ProcessAlreadyCalled' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessAlreadyCalled',
                'processAlreadyCalled'
            ],
            'ProcessNotReservedAnymore' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessNotReservedAnymore',
                'processNotReservedAnymore'
            ],
            'ProcessNotPreconfirmedAnymore' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessNotPreconfirmedAnymore',
                'processNotPreconfirmedAnymore'
            ],
            'ProcessDeleteFailed' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessDeleteFailed',
                'processDeleteFailed'
            ],
            'ProcessInvalid' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessInvalid',
                'processInvalid'
            ],
            'ProcessAlreadyExists' => [
                'BO\\Zmsbackend\\Process\\Exception\\ProcessAlreadyExists',
                'processAlreadyExists'
            ],
            'EmailRequired' => [
                'BO\\Zmsbackend\\Process\\Exception\\EmailRequired',
                'emailIsRequired'
            ],
            'TelephoneRequired' => [
                'BO\\Zmsbackend\\Process\\Exception\\TelephoneRequired',
                'telephoneIsRequired'
            ],
            'MoreThanAllowedAppointmentsPerMail' => [
                'BO\\Zmsbackend\\Process\\Exception\\MoreThanAllowedAppointmentsPerMail',
                'tooManyAppointmentsWithSameMail'
            ],
            'PreconfirmationExpired' => [
                'BO\\Zmsbackend\\Process\\Exception\\PreconfirmationExpired',
                'preconfirmationExpired'
            ],
            'ApiclientInvalid' => [
                'BO\\Zmsbackend\\Process\\Exception\\ApiclientInvalid',
                'invalidApiClient'
            ]
        ];
    }

    /**
     * @dataProvider calendarExceptionProvider
     */
    public function testCalendarExceptions(string $template, string $errorKey): void
    {
        $exception = new MockException('Test message', 0, $template);
        $expectedError = ErrorMessages::get($errorKey);
        
        try {
            ExceptionService::handleException($exception, 'testMethod');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                $expectedError['errorCode'] . ': ' . $expectedError['errorMessage'],
                $e->getMessage()
            );
            $this->assertEquals($expectedError['statusCode'], $e->getCode());
        }
    }

    public static function calendarExceptionProvider(): array
    {
        return [
            'InvalidFirstDay' => [
                'BO\\Zmsbackend\\Calendar\\Exception\\InvalidFirstDay',
                'invalidDateRange'
            ],
            'AppointmentsMissed' => [
                'BO\\Zmsbackend\\Calendar\\Exception\\AppointmentsMissed',
                'noAppointmentForThisScope'
            ],
            'CalendarWithoutScopes' => [
                'BO\\Zmsbackend\\Calendar\\Exception\\CalendarWithoutScopes',
                'noAppointmentForThisScope'
            ]
        ];
    }

    /**
     * @dataProvider entityExceptionProvider
     */
    public function testEntityExceptions(string $template, string $errorKey): void
    {
        $exception = new MockException('Test message', 0, $template);
        $expectedError = ErrorMessages::get($errorKey);
        
        try {
            ExceptionService::handleException($exception, 'testMethod');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(
                $expectedError['errorCode'] . ': ' . $expectedError['errorMessage'],
                $e->getMessage()
            );
            $this->assertEquals($expectedError['statusCode'], $e->getCode());
        }
    }

    public static function entityExceptionProvider(): array
    {
        return [
            'DepartmentNotFound' => [
                'BO\\Zmsbackend\\Department\\Exception\\DepartmentNotFound',
                'departmentNotFound'
            ],
            'MailNotFound' => [
                'BO\\Zmsbackend\\Mail\\Exception\\MailNotFound',
                'mailNotFound'
            ],
            'OrganisationNotFound' => [
                'BO\\Zmsbackend\\Organisation\\Exception\\OrganisationNotFound',
                'organisationNotFound'
            ],
            'ProviderNotFound' => [
                'BO\\Zmsbackend\\Provider\\Exception\\ProviderNotFound',
                'providerNotFound'
            ],
            'RequestNotFound' => [
                'BO\\Zmsbackend\\Request\\Exception\\RequestNotFound',
                'requestNotFound'
            ],
            'ScopeNotFound' => [
                'BO\\Zmsbackend\\Scope\\Exception\\ScopeNotFound',
                'scopeNotFound'
            ],
            'SourceNotFound' => [
                'BO\\Zmsbackend\\Source\\Exception\\SourceNotFound',
                'sourceNotFound'
            ]
        ];
    }

    public function testUnmappedException(): void
    {
        $exception = new MockException('Test message', 400, 'UnmappedException');

        try {
            ExceptionService::handleException($exception, 'testMethod');
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
            ExceptionService::handleException($exception, 'testMethod');
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
            ExceptionService::handleException($exception, 'testMethod');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(500, $e->getCode());
        }
    }
}