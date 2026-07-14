<?php

namespace BO\Zmsbackend\Calendar\Exception;

/**
 * example class to generate an exception
 */
class AppointmentsMissed extends \Exception
{
    protected $code = 404;

    public mixed $data = null;
}
