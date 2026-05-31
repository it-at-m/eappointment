<?php

namespace BO\Zmsapi\Exception\Request;

class RequestNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte keine Dienstleistung gefunden werden.';
}
