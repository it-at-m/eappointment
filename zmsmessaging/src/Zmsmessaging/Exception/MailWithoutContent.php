<?php

namespace BO\Zmsmessaging\Exception;

class MailWithoutContent extends \Exception
{
    protected $code = 428;

    protected $message = 'Queue entry without message content failure';
}
