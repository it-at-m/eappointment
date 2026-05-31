<?php

namespace BO\Zmsapi\Exception\Process;

class MoreThanAllowedSlotsPerAppointment extends \Exception
{
    protected int $code = 400;

    protected string $message = 'The number of slots exceeds the maximum allowed slots per appointment';
}
