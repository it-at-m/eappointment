<?php

namespace BO\Zmsapi\Exception\Availability;

/**
 * example class to generate an exception
 */
class AvailabilityListUpdateFailed extends \Exception
{
    protected int $code = 400;

    protected string $message = 'Could not insert or update availablity';
}
