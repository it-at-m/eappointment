<?php

namespace BO\Zmsapi\Exception\Process;

class ExternalUserIdMatchFailed extends \Exception
{
    protected int $code = 403;

    protected string $message = 'The process is not assigned to this external user id.';
}
