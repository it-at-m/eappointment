<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Core\Service;

use BO\Zmscitizenbackend\Schema\Exception\SchemaValidation;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class ExceptionService
{
    /**
     * @var array<string, string>
     */
    private const array TEMPLATE_ERRORS = [
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotFound' => 'appointmentNotFound',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\AuthKeyMatchFailed' => 'authKeyMismatch',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\ExternalUserIdMatchFailed' => 'authKeyMismatch',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotReservedAnymore' => 'processNotReservedAnymore',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotPreconfirmedAnymore' => 'processNotPreconfirmedAnymore',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessDeleteFailed' => 'processDeleteFailed',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\EmailRequired' => 'emailIsRequired',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\MoreThanAllowedAppointmentsPerMail'
            => 'tooManyAppointmentsWithSameMail',
        'BO\\Zmscitizenbackend\\Appointment\\Exception\\AppointmentNotAvailable' => 'appointmentNotAvailable',
        'BO\\Zmscitizenbackend\\Availability\\Exception\\InvalidAvailabilityInput' => 'invalidDateRange',
        'BO\\Zmscitizenbackend\\Availability\\Exception\\CalendarWithoutScopes' => 'noAppointmentForThisScope',
    ];

    /**
     * @return array<string, mixed>
     */
    private static function getError(string $key): array
    {
        return ErrorMessages::get($key);
    }

    public static function handleException(\Exception $e): never
    {
        if ($e instanceof SchemaValidation) {
            $error = self::getError('invalidSchema');
        } else {
            $exceptionName = json_decode(json_encode($e), true)['template'] ?? null;
            $errorKey = is_string($exceptionName)
                ? (self::TEMPLATE_ERRORS[$exceptionName] ?? null)
                : null;
            $error = $errorKey !== null ? self::getError($errorKey) : [
                'errorCode' => $exceptionName ?? 'unknown',
                'errorMessage' => $e->getMessage(),
                'statusCode' => $e->getCode() ?: 500,
            ];
        }

        throw new \RuntimeException($error['errorCode'] . ': ' . $error['errorMessage'], $error['statusCode'], $e);
    }
}
