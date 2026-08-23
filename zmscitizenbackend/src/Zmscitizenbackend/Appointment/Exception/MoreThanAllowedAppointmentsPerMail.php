<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class MoreThanAllowedAppointmentsPerMail extends \Exception
{
    protected $code = 429;

    protected $message = 'Too many appointments with the same E-mail address';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\MoreThanAllowedAppointmentsPerMail';
}
