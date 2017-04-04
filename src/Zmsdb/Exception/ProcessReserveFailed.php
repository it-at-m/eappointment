<?php

namespace BO\Zmsdb\Exception;

class ProcessReserveFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to reserve process. Maybe someone was faster.';
}
