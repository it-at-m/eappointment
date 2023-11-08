<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class DayoffWrongYear extends \Exception
{
    protected $code = 404;

    protected $message = 'A dayoff entity has date with wrong year';
}
