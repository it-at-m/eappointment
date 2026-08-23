<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class ExternalUserIdMatchFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'The process is not assigned to this external user id.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\ExternalUserIdMatchFailed';
}
