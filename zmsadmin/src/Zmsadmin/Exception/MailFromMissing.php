<?php

namespace BO\Zmsadmin\Exception;

class MailFromMissing extends \Exception
{
    protected int $code = 412;

    protected string $message = 'sender mail address required in department';
}
