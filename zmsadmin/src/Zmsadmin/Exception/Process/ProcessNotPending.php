<?php

namespace BO\Zmsadmin\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessNotPending extends \Exception
{
    protected $code = 428;
    protected $message = 'Process has not status pending, its not allowed to call it as pickup';
}
