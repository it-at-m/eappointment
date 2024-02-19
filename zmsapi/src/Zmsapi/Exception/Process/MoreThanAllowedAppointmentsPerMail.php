<?php

namespace BO\Zmsapi\Exception\Process;

class MoreThanAllowedAppointmentsPerMail extends \Exception
{
    protected $code = 429;

    protected $message = 'Too many appointments with the same E-mail address';
}