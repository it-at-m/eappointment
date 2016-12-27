<?php

namespace BO\Zmsapi\Exception\Notification;

/**
 * class to generate an exception if children exists
 */
class NotificationDeleteFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to delete notification';
}
