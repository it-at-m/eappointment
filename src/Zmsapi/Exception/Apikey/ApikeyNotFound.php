<?php

namespace BO\Zmsapi\Exception\Apikey;

/**
 * example class to generate an exception
 */
class ApikeyNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein aktiver Apikey gefunden werden';
}
