<?php

namespace BO\Zmsdb\Exception\Notification;

class WriteInQueueFailed extends \Exception
{
    protected $code = 500;

    protected $message = "Failed to write notification in queue";
}
