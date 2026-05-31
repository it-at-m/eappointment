<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessReserveFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to reserve process. Maybe someone was faster.';
}
