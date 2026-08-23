<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class AuthKeyMatchFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'Der Absagecode ist nicht korrekt.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\AuthKeyMatchFailed';
}
