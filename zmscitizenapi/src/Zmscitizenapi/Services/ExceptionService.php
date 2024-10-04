<?php

namespace BO\Zmscitizenapi\Services;

class ExceptionService
{

    public static function noAppointmentsAtLocation(){

        $errors[] = [
            'errorCode' => 'noAppointmentForThisOffice',
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'status' => 404,
        ];

        return ['errors' => $errors, 'status' => 404];

    }

    public static function appointmentNotFound(){

        $errors[] = [
            'errorCode' => 'appointmentNotFound',
            'errorMessage' => 'Termin wurde nicht gefunden.',
            'status' => 404,
        ];

        return ['errors' => $errors, 'status' => 404];

    }

    public static function tooManyAppointmentsWithSameMail(){
        $errors[] = [ 
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.',
            'status' => 406,
        ];

        return ['errors' => $errors, 'status' => 406];

    }

}