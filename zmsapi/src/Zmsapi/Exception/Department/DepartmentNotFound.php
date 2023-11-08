<?php

namespace BO\Zmsapi\Exception\Department;

/**
 * example class to generate an exception
 */
class DepartmentNotFound extends \Exception
{
    protected $code = 404;
    protected $message = 'Zu den angegebenen Daten konnte keine Behörde gefunden werden.';
}
