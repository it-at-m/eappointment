<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Core;

use BO\Zmscitizenbackend\Exceptions\SchemaValidation;
use BO\Zmscitizenbackend\Utils\ErrorMessages;

class ExceptionService
{
    /**
     * @var array<string, string>
     */
    private const array TEMPLATE_ERRORS = [
        'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotFound' => 'appointmentNotFound',
        'BO\\Zmscitizenbackend\\Exceptions\\AuthKeyMatchFailed' => 'authKeyMismatch',
        'BO\\Zmscitizenbackend\\Exceptions\\ExternalUserIdMatchFailed' => 'authKeyMismatch',
        'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotReservedAnymore' => 'processNotReservedAnymore',
        'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotPreconfirmedAnymore' => 'processNotPreconfirmedAnymore',
        'BO\\Zmscitizenbackend\\Exceptions\\ProcessDeleteFailed' => 'processDeleteFailed',
        'BO\\Zmscitizenbackend\\Exceptions\\EmailRequired' => 'emailIsRequired',
        'BO\\Zmscitizenbackend\\Exceptions\\MoreThanAllowedAppointmentsPerMail'
            => 'tooManyAppointmentsWithSameMail',
        'BO\\Zmscitizenbackend\\Exceptions\\AppointmentNotAvailable' => 'appointmentNotAvailable',
        'BO\\Zmscitizenbackend\\Exceptions\\InvalidAvailabilityInput' => 'invalidDateRange',
        'BO\\Zmscitizenbackend\\Exceptions\\CalendarWithoutScopes' => 'noAppointmentForThisScope',
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
