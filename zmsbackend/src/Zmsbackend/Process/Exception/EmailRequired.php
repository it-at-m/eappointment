<?php

namespace BO\Zmsbackend\Process\Exception;

/**
 * example class to generate an exception
 */
class EmailRequired extends \Exception
{
    protected $code = 400;

    protected $message = 'Für den Standort ist eine E-Mail-Adresse ein Pflichtfeld.';
}
