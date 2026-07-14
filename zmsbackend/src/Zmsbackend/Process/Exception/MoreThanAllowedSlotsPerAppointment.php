<?php

namespace BO\Zmsbackend\Process\Exception;

class MoreThanAllowedSlotsPerAppointment extends \Exception
{
    protected $code = 400;

    protected $message = 'The number of slots exceeds the maximum allowed slots per appointment';
}
