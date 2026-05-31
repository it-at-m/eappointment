<?php

namespace BO\Zmsapi\Exception\Mail;

/**
 * class to generate an exception if children exists
 */
class MailDeleteFailed extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Failed to delete mail';
}
