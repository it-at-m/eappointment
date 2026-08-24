<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class AppointmentNotAvailable extends \Exception
{
    protected $code = 404;

    protected $message = 'The selected appointment is unfortunately no longer available.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\AppointmentNotAvailable';
}
