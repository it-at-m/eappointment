<?php

namespace BO\Zmsdb\Exception;

class ProcessReserveFailed extends \Exception
{
    protected $error = 404;

    protected $message = 'Failed to reserve process. Maybe someone was faster.';
}
