<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class EmailRequired extends \Exception
{
    protected $code = 400;

    protected $message = 'Für den Standort ist eine E-Mail-Adresse ein Pflichtfeld.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\EmailRequired';
}
