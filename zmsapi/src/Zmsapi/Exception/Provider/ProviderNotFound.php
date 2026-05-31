<?php

namespace BO\Zmsapi\Exception\Provider;

class ProviderNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte kein Dienstleister gefunden werden.';
}
