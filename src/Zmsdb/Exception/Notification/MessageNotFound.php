<?php

namespace BO\Zmsdb\Exception\Notification;

class MessageNotFound extends \Exception
{
    protected $error = 500;

    protected $message = "No message found for notification";
}
