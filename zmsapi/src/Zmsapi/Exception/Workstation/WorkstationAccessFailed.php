<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationAccessFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'you are not allowed to access another workstation';
}
