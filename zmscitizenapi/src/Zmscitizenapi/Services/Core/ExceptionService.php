<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class ExceptionService
{
    // Common error response methods
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
        return ['errors' => [ErrorMessages::get('officesNotFound')]];
    }

    public static function servicesNotFound(): array
    {
        return ['errors' => [ErrorMessages::get('servicesNotFound')]];
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
        
        // Common exceptions across all methods
        switch ($exceptionName) {
            case 'BO\\Zmsapi\\Exception\\Process\\ApiclientInvalid':
                $error = ErrorMessages::get('invalidApiClient');
                break;
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
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid':
                $error = ErrorMessages::get('processInvalid');
                break;
            
            // Method-specific exceptions
            default:
                $error = self::handleMethodSpecificException($exceptionName, $method);
                break;
        }
    
        throw new \RuntimeException(
            $error['errorCode'] . ': ' . $error['errorMessage'],
            $error['statusCode'],
            $e
        );
    }
    
    private static function handleMethodSpecificException(?string $exceptionName, string $method): array
    {
        switch ($method) {
            case 'getOffices':
            case 'getScopes':
            case 'getServices':
            case 'getRequestRelationList':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Source\\SourceNotFound') {
                    return ErrorMessages::get('internalError');
                }
                break;

            case 'getFreeDays':
            case 'getFreeTimeslots':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Calendar\\InvalidFirstDay') {
                    return ErrorMessages::get('invalidDateRange');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Calendar\\AppointmentsMissed') {
                    return ErrorMessages::get('noAppointmentsAtLocation');
                }
                break;
    
            case 'reserveTimeslot':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyExists') {
                    return ErrorMessages::get('processAlreadyExists');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\EmailRequired') {
                    return ErrorMessages::get('emailIsRequired');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired') {
                    return ErrorMessages::get('telephoneIsRequired');
                }
                break;
    
            case 'submitClientData':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail') {
                    return ErrorMessages::get('tooManyAppointmentsWithSameMail');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\EmailRequired') {
                    return ErrorMessages::get('emailIsRequired');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired') {
                    return ErrorMessages::get('telephoneIsRequired');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore') {
                    return ErrorMessages::get('processNotReservedAnymore');
                }
                break;
    
            case 'preconfirmProcess':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\PreconfirmationExpired') {
                    return ErrorMessages::get('preconfirmationExpired');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail') {
                    return ErrorMessages::get('tooManyAppointmentsWithSameMail');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore') {
                    return ErrorMessages::get('processNotReservedAnymore');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled') {
                    return ErrorMessages::get('processAlreadyCalled');
                }
                break;
    
            case 'confirmProcess':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail') {
                    return ErrorMessages::get('tooManyAppointmentsWithSameMail');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore') {
                    return ErrorMessages::get('processNotReservedAnymore');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotPreconfirmedAnymore') {
                    return ErrorMessages::get('processNotPreconfirmedAnymore');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled') {
                    return ErrorMessages::get('processAlreadyCalled');
                }
                break;
    
            case 'cancelAppointment':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessDeleteFailed') {
                    return ErrorMessages::get('processDeleteFailed');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore') {
                    return ErrorMessages::get('processNotReservedAnymore');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotPreconfirmedAnymore') {
                    return ErrorMessages::get('processNotPreconfirmedAnymore');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled') {
                    return ErrorMessages::get('processAlreadyCalled');
                }
                break;
    
            case 'getProcessById':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
            case 'sendConfirmationEmail':
            case 'sendPreconfirmationEmail':
            case 'sendCancelationEmail':
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound') {
                    return ErrorMessages::get('appointmentNotFound');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed') {
                    return ErrorMessages::get('authKeyMismatch');
                }
                if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled') {
                    return ErrorMessages::get('processAlreadyCalled');
                }
                break;
        }
    
        return ErrorMessages::get('internalError');
    }
}