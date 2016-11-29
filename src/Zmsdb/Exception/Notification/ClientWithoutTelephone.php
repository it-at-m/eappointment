<?php

namespace BO\Zmsdb\Exception\Notification;

class ClientWithoutTelephone extends \Exception
{
    protected $error = 500;

    protected $message = "No telephone found for notification";
}
