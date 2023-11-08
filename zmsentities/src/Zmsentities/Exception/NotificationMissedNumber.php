<?php

namespace BO\Zmsentities\Exception;

class NotificationMissedNumber extends \Exception
{
    protected $code = 500;

    protected $message = "Missed client phone number for sending notification";
}
