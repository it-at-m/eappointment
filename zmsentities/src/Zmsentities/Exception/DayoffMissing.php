<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class DayoffMissing extends \Exception
{
    protected $code = 500;

    protected $message = 'Dayoff data is missing but is required';
}
