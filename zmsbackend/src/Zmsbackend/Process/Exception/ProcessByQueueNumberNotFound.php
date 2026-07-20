<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessByQueueNumberNotFound extends \Exception
{
    protected $code = 404;
    protected $message = 'Zu der angegebenen Wartenummer existiert kein Vorgang an diesem Standort.';
}
