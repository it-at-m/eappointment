<?php

namespace BO\Zmsapi\Exception\Process;

class MoreThanAllowedSlotsPerAppointment extends \Exception
{
    protected $code = 400;

    protected $message = 'The number of slots exceeds the maximum allowed slots per appointment';
}
