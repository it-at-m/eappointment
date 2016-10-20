<?php

namespace BO\Zmsapi\Exception\Provider;

/**
 * example class to generate an exception
 */
class ProviderNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein Dienstleister gefunden werden.';
}
