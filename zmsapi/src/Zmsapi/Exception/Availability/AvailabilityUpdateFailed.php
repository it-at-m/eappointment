<?php

namespace BO\Zmsapi\Exception\Availability;

/**
 * example class to generate an exception
 */
class AvailabilityUpdateFailed extends \Exception
{
    protected $code = 400;

    protected $message = 'Could not insert or update availablity';
}
