<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class ExceptionService
{
    private static ?string $currentLanguage = null;
    public static function setLanguageContext(?string $language): void
    {
        self::$currentLanguage = $language;
    }

    public static function getLanguageContext(): ?string
    {
        return self::$currentLanguage;
    }

    private static function getError(string $key): array
    {
        return  ErrorMessages::get($key, self::$currentLanguage);
    }

    public static function handleException(\Exception $e): never
    {
        $exceptionName = json_decode(json_encode($e), true)['template'] ?? null;
        $error = null;
        switch ($exceptionName) {
        // Process exceptions
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $error = self::getError('appointmentNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $error = self::getError('authKeyMismatch');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyCalled':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processAlreadyCalled');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processNotReservedAnymore');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessNotPreconfirmedAnymore':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processNotPreconfirmedAnymore');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessDeleteFailed':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processDeleteFailed');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processInvalid');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyExists':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('processAlreadyExists');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\EmailRequired':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('emailIsRequired');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('telephoneIsRequired');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('tooManyAppointmentsWithSameMail');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\PreconfirmationExpired':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('preconfirmationExpired');

                break;
            case 'BO\\Zmsapi\\Exception\\Process\\ApiclientInvalid':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('invalidApiClient');

                break;
// Calendar exceptions
            case 'BO\\Zmsapi\\Exception\\Calendar\\InvalidFirstDay':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $error = self::getError('invalidDateRange');

                break;
            case 'BO\\Zmsapi\\Exception\\Calendar\\AppointmentsMissed':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('noAppointmentsAtLocation');

                break;
// Other entity exceptions
            case 'BO\\Zmsapi\\Exception\\Department\\DepartmentNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $error = self::getError('departmentNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Mail\\MailNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('mailNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Organisation\\OrganisationNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('organisationNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Provider\\ProviderNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('providerNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Request\\RequestNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('requestNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Scope\\ScopeNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('scopeNotFound');

                break;
            case 'BO\\Zmsapi\\Exception\\Source\\SourceNotFound':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $error = self::getError('sourceNotFound');

                break;
// Use original message for unmapped exceptions
            default:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $error = [
                'errorCode' => $exceptionName ?? 'unknown',
                'errorMessage' => $e->getMessage(),
                'statusCode' => $e->getCode() ?: 500
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          ];
        }

        throw new \RuntimeException($error['errorCode'] . ': ' . $error['errorMessage'], $error['statusCode'], $e);
    }
}
