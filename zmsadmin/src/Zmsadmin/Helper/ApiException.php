<?php

namespace BO\Zmsadmin\Helper;

class ApiException
{
    public static function templateEndsWith(\BO\Zmsclient\Exception $exception, string $exceptionClass): bool
    {
        if (empty($exception->template)) {
            return false;
        }

        return str_ends_with($exception->template, '\\' . ltrim($exceptionClass, '\\'));
    }
}
