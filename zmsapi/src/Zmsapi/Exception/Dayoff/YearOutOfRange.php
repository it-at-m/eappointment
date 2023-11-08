<?php

namespace BO\Zmsapi\Exception\Dayoff;

/**
 * example class to generate an exception
 */
class YearOutOfRange extends \Exception
{
    protected $code = 404;

    protected $message = 'Given year is out of acceptable range (max. +10 years allowed)';
}
