<?php

namespace BO\Zmsapi\Exception\Mail;

/**
 * class to generate an exception if children exists
 */
class MailNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Mail does not exists';
}
