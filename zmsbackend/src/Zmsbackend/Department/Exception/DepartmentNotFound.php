<?php

namespace BO\Zmsbackend\Department\Exception;

/**
 * example class to generate an exception
 */
class DepartmentNotFound extends \Exception
{
    protected $code = 404;
    protected $message = 'Zu den angegebenen Daten konnte keine Behörde gefunden werden.';
}
