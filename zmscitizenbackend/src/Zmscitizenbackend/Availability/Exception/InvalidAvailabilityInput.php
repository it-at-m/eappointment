<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Exception;

class InvalidAvailabilityInput extends \Exception
{
    public string $template = 'BO\\Zmscitizenbackend\\Availability\\Exception\\InvalidAvailabilityInput';
}
