<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class DayoffWrongYear extends \Exception
{
    protected int $code = 404;

    protected string $message = 'A dayoff entity has date with wrong year';
}
