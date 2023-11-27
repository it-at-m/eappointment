<?php

namespace BO\Zmsdb\Exception\Notification;

class MessageNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "No message found for notification";
}
