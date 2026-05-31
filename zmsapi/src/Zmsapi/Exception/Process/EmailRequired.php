<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class EmailRequired extends \Exception
{
    protected int $code = 400;

    protected string $message = 'Für den Standort ist eine E-Mail-Adresse ein Pflichtfeld.';
}
