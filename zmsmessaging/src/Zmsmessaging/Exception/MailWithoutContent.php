<?php

namespace BO\Zmsmessaging\Exception;

class MailWithoutContent extends \Exception
{
    protected int $code = 428;

    protected string $message = 'Queue entry without message content failure';
}
