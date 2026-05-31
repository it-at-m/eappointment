<?php

namespace BO\Zmsmessaging\Exception;

class SendingFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Sending Mail failed - Unknow Error';
}
