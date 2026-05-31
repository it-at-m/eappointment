<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationAuthFailed extends \Exception
{
    protected int $code = 403;

    protected string $message = 'no valid auth method found';
}
