<?php

namespace BO\Zmsdb\Exception\Notification;

class ClientWithoutTelephone extends \Exception
{
    protected $code = 404;

    protected $message = "No telephone found for notification";
}
