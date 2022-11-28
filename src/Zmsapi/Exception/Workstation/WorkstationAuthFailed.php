<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationAuthFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'no valid auth method found';
}
