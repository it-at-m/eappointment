<?php

namespace BO\Zmsbackend\Provider\Exception;

class ProviderNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein Dienstleister gefunden werden.';
}
