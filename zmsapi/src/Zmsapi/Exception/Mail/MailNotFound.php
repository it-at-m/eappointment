<?php

namespace BO\Zmsapi\Exception\Mail;

/**
 * class to generate an exception if children exists
 */
class MailNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Mail does not exists';
}
