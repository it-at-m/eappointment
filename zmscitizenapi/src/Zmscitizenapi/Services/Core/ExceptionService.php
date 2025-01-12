<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class ExceptionService
{

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