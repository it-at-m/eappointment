<?php

namespace BO\Zmscitizenapi\Services;

class ExceptionService
{

    public static function exceptionNoAppointmentsAtLocation(){

        $errors[] = [
            'errorCode' => 'noAppointmentForThisOffice',
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'status' => 404,
        ];

        return ['errors' => $errors, 'status' => 404];

    }

}