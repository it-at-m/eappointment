<?php

namespace BO\Zmsapi\Exception\Availability;

/**
 * example class to generate an exception
 */
class AvailabilityAddFailed extends \Exception
{
    protected $code = 400;

    protected $message = 'Could not create availablity.';
}
