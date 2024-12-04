<?php

namespace BO\Zmsapi\Exception\Availability;

/**
 * example class to generate an exception
 */
class AvailabilityAddFailed extends \Exception
{
    protected $code = 400;

    protected $message = 'Failed to create availability. Please ensure there are no conflicts with existing entries.';

}
