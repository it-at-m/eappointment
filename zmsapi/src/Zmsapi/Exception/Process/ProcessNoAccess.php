<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessNoAccess extends \Exception
{
    protected int $code = 403;
    protected string $message = 'Process scope does not match scope of current workstation';
}
