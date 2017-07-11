<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessNotFoundInQueue extends \Exception
{
    protected $code = 404;
    protected $message = 'There is no waiting client at this moment';
}
