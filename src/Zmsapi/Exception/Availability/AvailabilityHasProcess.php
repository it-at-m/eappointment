<?php

namespace BO\Zmsapi\Exception\Availability;

class AvailabilityHasProcess extends \Exception
{
    protected $code = 412;

    protected $message = 'Diese Öffnungszeit kann nicht gelöscht werden, da dieser noch Vorgänge zugeordnet sind. Dies können gebuchte Termine oder Reservierungen sein.';
}
