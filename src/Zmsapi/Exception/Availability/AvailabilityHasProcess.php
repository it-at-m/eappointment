<?php

namespace BO\Zmsapi\Exception\Availability;

class AvailabilityHasProcess extends \Exception
{
    protected $code = 412;

    protected $message = 'Dieser Öffnungszeit sind noch Termine oder Reservierungen zugeordnet.';
}
