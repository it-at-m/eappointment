<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessReserveFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to reserve process. Maybe someone was faster.';
}
