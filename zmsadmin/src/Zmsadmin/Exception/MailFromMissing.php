<?php

namespace BO\Zmsadmin\Exception;

class MailFromMissing extends \Exception
{
    protected $code = 412;

    protected $message = 'sender mail address required in department';
}
