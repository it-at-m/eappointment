<?php

namespace BO\Zmsapi\Exception\Notification;

/**
 * class to generate an exception if children exists
 */
class NotificationNotFound extends \Exception
{

    protected $code = 404;

    protected $message = 'Notification does not exists';
}
