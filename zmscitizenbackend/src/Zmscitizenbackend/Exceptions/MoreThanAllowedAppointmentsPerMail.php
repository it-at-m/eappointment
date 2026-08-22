<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class MoreThanAllowedAppointmentsPerMail extends \Exception
{
    protected $code = 429;

    protected $message = 'Too many appointments with the same E-mail address';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\MoreThanAllowedAppointmentsPerMail';
}
