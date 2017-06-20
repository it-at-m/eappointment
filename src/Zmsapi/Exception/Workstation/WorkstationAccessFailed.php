<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationAccessFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'you are not allowed to access another workstation';
}
