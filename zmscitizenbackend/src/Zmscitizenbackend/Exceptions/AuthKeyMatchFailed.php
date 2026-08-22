<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class AuthKeyMatchFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'Der Absagecode ist nicht korrekt.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\AuthKeyMatchFailed';
}
