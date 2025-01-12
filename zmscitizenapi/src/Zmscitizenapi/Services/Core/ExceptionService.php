<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class ExceptionService
{
    public static function noAppointmentsAtLocation(): array
    {
        return ['errors' => [ErrorMessages::get('noAppointmentsAtLocation')]];
    }

    public static function appointmentNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('appointmentNotFound')]];
    }

    public static function authKeyMismatch(): array
    {
        return ['errors' => [ErrorMessages::get('authKeyMismatch')]];
    }

    public static function preconfirmationExpired(): array
    {
        return ['errors' => [ErrorMessages::get('preconfirmationExpired')]];
    }

    public static function tooManyAppointmentsWithSameMail(): array
    {
        return ['errors' => [ErrorMessages::get('tooManyAppointmentsWithSameMail')]];
    }

    public static function officesNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('providerNotFound')]];
    }

    public static function servicesNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('requestNotFound')]];
    }

    public static function scopesNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('scopesNotFound')]];
    }

    public static function processInvalid(): array
    {
        return ['errors' => [ErrorMessages::get('processInvalid')]];
    }

    public static function processAlreadyExists(): array
    {
        return ['errors' => [ErrorMessages::get('processAlreadyExists')]];
    }

    public static function processDeleteFailed(): array
    {
        return ['errors' => [ErrorMessages::get('processDeleteFailed')]];
    }

    public static function processAlreadyCalled(): array
    {
        return ['errors' => [ErrorMessages::get('processAlreadyCalled')]];
    }

    public static function processNotReservedAnymore(): array
    {
        return ['errors' => [ErrorMessages::get('processNotReservedAnymore')]];
    }

    public static function processNotPreconfirmedAnymore(): array
    {
        return ['errors' => [ErrorMessages::get('processNotPreconfirmedAnymore')]];
    }

    public static function emailIsRequired(): array
    {
        return ['errors' => [ErrorMessages::get('emailIsRequired')]];
    }

    public static function telephoneIsRequired(): array
    {
        return ['errors' => [ErrorMessages::get('telephoneIsRequired')]];
    }

    public static function internalError(): array
    {
        return ['errors' => [ErrorMessages::get('internalError')]];
    }

    public static function invalidApiClient(): array
    {
        return ['errors' => [ErrorMessages::get('invalidApiClient')]];
    }

    public static function departmentNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('departmentNotFound')]];
    }

    public static function mailNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('mailNotFound')]];
    }

    public static function organisationNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('organisationNotFound')]];
    }

    public static function providerNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('providerNotFound')]];
    }

    public static function requestNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('requestNotFound')]];
    }

    public static function scopeNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('scopeNotFound')]];
    }

    public static function handleException(\Exception $e, string $method): never
    {
        $exceptionName = json_decode(json_encode($e), true)['template'] ?? null;
        $error = null;

        switch ($exceptionName) {
            // Process exceptions
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound':
                $error = ErrorMessages::get('appointmentNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed':
                $error = ErrorMessages::get('authKeyMismatch');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled':
                $error = ErrorMessages::get('processAlreadyCalled');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore':
                $error = ErrorMessages::get('processNotReservedAnymore');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotPreconfirmedAnymore':
                $error = ErrorMessages::get('processNotPreconfirmedAnymore');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessDeleteFailed':
                $error = ErrorMessages::get('processDeleteFailed');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid':
                $error = ErrorMessages::get('processInvalid');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyExists':
                $error = ErrorMessages::get('processAlreadyExists');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\EmailRequired':
                $error = ErrorMessages::get('emailIsRequired');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired':
                $error = ErrorMessages::get('telephoneIsRequired');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail':
                $error = ErrorMessages::get('tooManyAppointmentsWithSameMail');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\PreconfirmationExpired':
                $error = ErrorMessages::get('preconfirmationExpired');
                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ApiclientInvalid':
                $error = ErrorMessages::get('invalidApiClient');
                break;

            // Calendar exceptions
            case 'BO\\Zmsapi\\Exception\\Calendar\\InvalidFirstDay':
                $error = ErrorMessages::get('invalidDateRange');
                break;
            case 'BO\\Zmsapi\\Exception\\Calendar\\AppointmentsMissed':
                $error = ErrorMessages::get('noAppointmentsAtLocation');
                break;

            // Other entity exceptions
            case 'BO\\Zmsapi\\Exception\\Department\\DepartmentNotFound':
                $error = ErrorMessages::get('departmentNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Mail\\MailNotFound':
                $error = ErrorMessages::get('mailNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Organisation\\OrganisationNotFound':
                $error = ErrorMessages::get('organisationNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Provider\\ProviderNotFound':
                $error = ErrorMessages::get('providerNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Request\\RequestNotFound':
                $error = ErrorMessages::get('requestNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Scope\\ScopeNotFound':
                $error = ErrorMessages::get('scopeNotFound');
                break;
            case 'BO\\Zmsapi\\Exception\\Source\\SourceNotFound':
                $error = ErrorMessages::get('sourceNotFound');
                break;

            // Use original message for unmapped exceptions
            default:
                $error = [
                    'errorCode' => $exceptionName ?? 'unknown',
                    'errorMessage' => $e->getMessage(),
                    'statusCode' => $e->getCode() ?: 500
                ];
        }

        throw new \RuntimeException(
            $error['errorCode'] . ': ' . $error['errorMessage'],
            $error['statusCode'],
            $e
        );
    }
}