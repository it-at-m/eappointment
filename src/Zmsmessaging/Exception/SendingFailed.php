<?php

namespace BO\Zmsmessaging\Exception;

class SendingFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Sending Mail or Notification failed - Unknow Error';
}
