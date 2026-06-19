<?php

namespace BO\Zmsbackend\Workstation\Exception;

class WorkstationAuthFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'no valid auth method found';
}
