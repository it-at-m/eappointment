<?php

namespace BO\Zmsapi\Exception\Mail;

/**
 * class to generate an exception if children exists
 */
class MailSenderFromMissing extends \Exception
{
    protected $code = 404;

    protected $message = 'There is no sender address specified for sending mail';
}
