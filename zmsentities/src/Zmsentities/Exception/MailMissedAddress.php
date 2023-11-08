<?php

namespace BO\Zmsentities\Exception;

class MailMissedAddress extends \Exception
{
    protected $code = 500;

    protected $message = "Missed client mail address for sending mail";
}
