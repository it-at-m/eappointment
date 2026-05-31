<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessReserveFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to reserve process. Maybe someone was faster.';
}
