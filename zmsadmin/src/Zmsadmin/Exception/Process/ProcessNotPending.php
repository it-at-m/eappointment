<?php

namespace BO\Zmsadmin\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessNotPending extends \Exception
{
    protected int $code = 428;
    protected string $message = 'Process has not status pending, its not allowed to call it as pickup';
}
