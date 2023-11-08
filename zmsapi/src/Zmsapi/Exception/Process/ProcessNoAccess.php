<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessNoAccess extends \Exception
{
    protected $code = 403;
    protected $message = 'Process scope does not match scope of current workstation';
}
