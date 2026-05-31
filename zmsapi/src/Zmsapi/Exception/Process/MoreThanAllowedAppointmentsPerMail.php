<?php

namespace BO\Zmsapi\Exception\Process;

class MoreThanAllowedAppointmentsPerMail extends \Exception
{
    protected int $code = 429;

    protected string $message = 'Too many appointments with the same E-mail address';
}
