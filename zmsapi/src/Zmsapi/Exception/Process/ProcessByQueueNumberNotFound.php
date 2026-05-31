<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessByQueueNumberNotFound extends \Exception
{
    protected int $code = 404;
    protected string $message = 'Zu der angegebenen Wartenummer existiert kein Vorgang an diesem Standort.';
}
