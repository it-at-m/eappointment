<?php

namespace BO\Zmsbackend\Availability\Exception;

/**
 * example class to generate an exception
 */
class AvailabilityListUpdateFailed extends \Exception
{
    protected $code = 400;

    protected $message = 'Could not insert or update availablity';
}
