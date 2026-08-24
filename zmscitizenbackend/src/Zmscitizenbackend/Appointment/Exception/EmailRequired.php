<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class EmailRequired extends \Exception
{
    protected $code = 400;

    protected $message = 'Für den Standort ist eine E-Mail-Adresse ein Pflichtfeld.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\EmailRequired';
}
