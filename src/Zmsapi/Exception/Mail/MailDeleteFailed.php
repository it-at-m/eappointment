<?php

namespace BO\Zmsapi\Exception\Mail;

/**
 * class to generate an exception if children exists
 */
class MailDeleteFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to delete mail';
}
