<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmsclient\Psr7\RequestException;

class ExceptionService
{
    private static function getError(string $key): array
    {
        return ErrorMessages::get($key);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @TODO: Consider using a strategy pattern or error handler chain to reduce method complexity
     */
    public static function handleException(\Exception $e): never
    {
        $exceptionName = json_decode(json_encode($e), true)['template'] ?? null;

        if ($e instanceof RequestException) {
            $error = self::getError('zmsClientCommunicationError');
        } else {
            switch ($exceptionName) {
            // Zmsslim exception
                case 'Slim\\Exception\\HttpNotFoundException':
                    $error = self::getError('notFound');

                    break;

            // ZmsClient exception
                case 'BO\\Zmsclient\\Exception':
                    $error = self::getError('zmsClientCommunicationError');

                    break;
            // Missing mail template exceptions
                case 'Twig\\Error\\RuntimeError':
                    $error = self::getError('mailNotFound');

                    break;
                case 'Twig\\Error\\LoaderError':
                    $error = self::getError('mailNotFound');

                    break;
                case 'BO\\Zmsbackend\\Mail\\Exception\\MailNotFound':
                    $error = self::getError('mailNotFound');

                    break;
            // Process exceptions
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessNotFound':
                    $error = self::getError('appointmentNotFound');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\AuthKeyMatchFailed':
                case 'BO\\Zmsbackend\\Process\\Exception\\ExternalUserIdMatchFailed':
                    $error = self::getError('authKeyMismatch');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessAlreadyCalled':
                    $error = self::getError('processAlreadyCalled');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessNotReservedAnymore':
                    $error = self::getError('processNotReservedAnymore');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessNotPreconfirmedAnymore':
                    $error = self::getError('processNotPreconfirmedAnymore');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessDeleteFailed':
                    $error = self::getError('processDeleteFailed');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessInvalid':
                    $error = self::getError('processInvalid');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ProcessAlreadyExists':
                    $error = self::getError('processAlreadyExists');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\EmailRequired':
                    $error = self::getError('emailIsRequired');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\TelephoneRequired':
                    $error = self::getError('telephoneIsRequired');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\MoreThanAllowedAppointmentsPerMail':
                    $error = self::getError('tooManyAppointmentsWithSameMail');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\MoreThanAllowedSlotsPerAppointment':
                    $error = self::getError('tooManySlotsPerAppointment');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\MoreThanAllowedQuantityPerService':
                    $error = self::getError('tooManyServicesPerAppointment');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\PreconfirmationExpired':
                    $error = self::getError('preconfirmationExpired');

                    break;
                case 'BO\\Zmsbackend\\Process\\Exception\\ApiclientInvalid':
                    $error = self::getError('invalidApiClient');

                    break;
            // Calendar exceptions
                case 'BO\\Zmsbackend\\Calendar\\Exception\\InvalidFirstDay':
                    $error = self::getError('invalidDateRange');

                    break;
                case 'BO\\Zmsbackend\\Calendar\\Exception\\AppointmentsMissed':
                    $error = self::getError('noAppointmentForThisScope');
                    break;
                case 'BO\\Zmsbackend\\Calendar\\Exception\\CalendarWithoutScopes':
                    $error = self::getError('noAppointmentForThisScope');
                    break;
            // Other entity exceptions
                case 'BO\\Zmsbackend\\Department\\Exception\\DepartmentNotFound':
                    $error = self::getError('departmentNotFound');

                    break;
                case 'BO\\Zmsbackend\\Organisation\\Exception\\OrganisationNotFound':
                    $error = self::getError('organisationNotFound');

                    break;
                case 'BO\\Zmsbackend\\Provider\\Exception\\ProviderNotFound':
                    $error = self::getError('providerNotFound');

                    break;
                case 'BO\\Zmsbackend\\Request\\Exception\\RequestNotFound':
                    $error = self::getError('requestNotFound');

                    break;
                case 'BO\\Zmsbackend\\Request\\Exception\\RequestNotFound':
                    $error = self::getError('requestNotFound');

                    break;
                case 'BO\\Zmsbackend\\Scope\\Exception\\ScopeNotFound':
                    $error = self::getError('scopeNotFound');

                    break;
                case 'BO\\Zmsbackend\\Source\\Exception\\SourceNotFound':
                    $error = self::getError('sourceNotFound');

                    break;
                case 'BO\\Zmsentities\\Exception\\SchemaValidation':
                    $error = self::getError('invalidSchema');

                    break;
                case 'BO\\Zmsbackend\\Useraccount\\Exception\\InvalidCredentials':
                    $error = self::getError('invalidCredentials');

                    break;

            // Use original message for unmapped exceptions
                default:
                    $error = [
                    'errorCode' => $exceptionName ?? 'unknown',
                    'errorMessage' => $e->getMessage(),
                    'statusCode' => $e->getCode() ?: 500
                    ];
            }
        }

        throw new \RuntimeException($error['errorCode'] . ': ' . $error['errorMessage'], $error['statusCode'], $e);
    }
}
