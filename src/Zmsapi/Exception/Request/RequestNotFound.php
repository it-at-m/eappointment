<?php

namespace BO\Zmsapi\Exception\Request;

class RequestNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Dienstleistung gefunden werden.';
}
