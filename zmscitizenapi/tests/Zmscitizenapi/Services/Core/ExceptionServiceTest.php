<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;
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

    public function processExceptionProvider(): array
    {
        return [
            'ProcessNotFound' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound',
                'appointmentNotFound'
            ],
            'AuthKeyMatchFailed' => [
                'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed',
                'authKeyMismatch'
            ],
            'ProcessAlreadyCalled' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled',
                'processAlreadyCalled'
            ],
            'ProcessNotReservedAnymore' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore',
                'processNotReservedAnymore'
            ],
            'ProcessNotPreconfirmedAnymore' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessNotPreconfirmedAnymore',
                'processNotPreconfirmedAnymore'
            ],
            'ProcessDeleteFailed' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessDeleteFailed',
                'processDeleteFailed'
            ],
            'ProcessInvalid' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid',
                'processInvalid'
            ],
            'ProcessAlreadyExists' => [
                'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyExists',
                'processAlreadyExists'
            ],
            'EmailRequired' => [
                'BO\\Zmsapi\\Exception\\Process\\EmailRequired',
                'emailIsRequired'
            ],
            'TelephoneRequired' => [
                'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired',
                'telephoneIsRequired'
            ],
            'MoreThanAllowedAppointmentsPerMail' => [
                'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail',
                'tooManyAppointmentsWithSameMail'
            ],
            'PreconfirmationExpired' => [
                'BO\\Zmsapi\\Exception\\Process\\PreconfirmationExpired',
                'preconfirmationExpired'
            ],
            'ApiclientInvalid' => [
                'BO\\Zmsapi\\Exception\\Process\\ApiclientInvalid',
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

    public function calendarExceptionProvider(): array
    {
        return [
            'InvalidFirstDay' => [
                'BO\\Zmsapi\\Exception\\Calendar\\InvalidFirstDay',
                'invalidDateRange'
            ],
            'AppointmentsMissed' => [
                'BO\\Zmsapi\\Exception\\Calendar\\AppointmentsMissed',
                'noAppointmentsAtLocation'
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

    public function entityExceptionProvider(): array
    {
        return [
            'DepartmentNotFound' => [
                'BO\\Zmsapi\\Exception\\Department\\DepartmentNotFound',
                'departmentNotFound'
            ],
            'MailNotFound' => [
                'BO\\Zmsapi\\Exception\\Mail\\MailNotFound',
                'mailNotFound'
            ],
            'OrganisationNotFound' => [
                'BO\\Zmsapi\\Exception\\Organisation\\OrganisationNotFound',
                'organisationNotFound'
            ],
            'ProviderNotFound' => [
                'BO\\Zmsapi\\Exception\\Provider\\ProviderNotFound',
                'providerNotFound'
            ],
            'RequestNotFound' => [
                'BO\\Zmsapi\\Exception\\Request\\RequestNotFound',
                'requestNotFound'
            ],
            'ScopeNotFound' => [
                'BO\\Zmsapi\\Exception\\Scope\\ScopeNotFound',
                'scopeNotFound'
            ],
            'SourceNotFound' => [
                'BO\\Zmsapi\\Exception\\Source\\SourceNotFound',
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