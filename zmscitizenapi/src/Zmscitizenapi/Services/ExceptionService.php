<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class ExceptionService
{

    private const HTTP_NOT_FOUND = 404;
    private const HTTP_NOT_ACCEPTABLE = 406;


    public static function noAppointmentsAtLocation(): array
    {

        $errors[] = ErrorMessages::get('noAppointmentForThisScope');

        return ['errors' => $errors];

    }

    public static function appointmentNotFound(): array
    {

        $errors[] = ErrorMessages::get('appointmentNotFound');

        return ['errors' => $errors];

    }

    public static function authKeyMissMatch(): array
    {
        $errors[] = ErrorMessages::get('authKeyMissMatch');

        return ['errors' => $errors];

    }

    public static function tooManyAppointmentsWithSameMail(): array
    {
        $errors[] = ErrorMessages::get('tooManyAppointmentsWithSameMail');

        return ['errors' => $errors];

    }

}