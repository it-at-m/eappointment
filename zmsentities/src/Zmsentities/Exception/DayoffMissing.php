<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class DayoffMissing extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Dayoff data is missing but is required';
}
