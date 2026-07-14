<?php

namespace BO\Zmsbackend\Process\Exception;

class ExternalUserIdMatchFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'The process is not assigned to this external user id.';
}
