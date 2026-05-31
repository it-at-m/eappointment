<?php

namespace BO\Zmsapi\Exception\Calendar;

/**
 * example class to generate an exception
 */
class AppointmentsMissed extends \Exception
{
    protected int $code = 404;

    public mixed $data = null;
}
