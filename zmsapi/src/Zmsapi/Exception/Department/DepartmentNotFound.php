<?php

namespace BO\Zmsapi\Exception\Department;

/**
 * example class to generate an exception
 */
class DepartmentNotFound extends \Exception
{
    protected int $code = 404;
    protected string $message = 'Zu den angegebenen Daten konnte keine Behörde gefunden werden.';
}
