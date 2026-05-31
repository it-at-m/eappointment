<?php

namespace BO\Zmsentities\Exception;

class MailMissedAddress extends \Exception
{
    protected int $code = 500;

    protected string $message = "Missed client mail address for sending mail";
}
