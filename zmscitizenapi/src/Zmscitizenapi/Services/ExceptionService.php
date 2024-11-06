<?php

namespace BO\Zmscitizenapi\Services;

class ExceptionService
{

    private const HTTP_NOT_FOUND = 404;
    private const HTTP_NOT_ACCEPTABLE = 406;

    private const ERROR_NO_APPOINTMENTS = 'noAppointmentForThisOffice';
    private const ERROR_APPOINTMENT_NOT_FOUND = 'appointmentNotFound';
    private const ERROR_TOO_MANY_APPOINTMENTS = 'tooManyAppointmentsWithSameMail';

    public static function noAppointmentsAtLocation(){

        $errors[] = [
            'errorCode' => self::ERROR_NO_APPOINTMENTS,
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'status' => self::HTTP_NOT_FOUND,
        ];

        return ['errors' => $errors, 'status' => self::HTTP_NOT_FOUND];

    }

    public static function appointmentNotFound(){

        $errors[] = [
            'errorCode' => self::ERROR_APPOINTMENT_NOT_FOUND,
            'errorMessage' => 'Termin wurde nicht gefunden.',
            'status' => self::HTTP_NOT_FOUND,
        ];

        return ['errors' => $errors, 'status' => self::HTTP_NOT_FOUND];

    }

    public static function tooManyAppointmentsWithSameMail(){
        $errors[] = [ 
            'errorCode' => self::ERROR_TOO_MANY_APPOINTMENTS,
            'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.',
            'status' => self::HTTP_NOT_ACCEPTABLE,
        ];

        return ['errors' => $errors, 'status' => self::HTTP_NOT_ACCEPTABLE];

    }

}