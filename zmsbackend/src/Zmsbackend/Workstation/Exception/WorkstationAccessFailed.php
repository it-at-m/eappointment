<?php

namespace BO\Zmsbackend\Workstation\Exception;

class WorkstationAccessFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'you are not allowed to access another workstation';
}
