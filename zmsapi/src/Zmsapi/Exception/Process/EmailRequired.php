<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class EmailRequired extends \Exception
{
    protected $code = 400;

    protected $message = 'Für den Standort ist eine E-Mail-Adresse ein Pflichtfeld.';
}
