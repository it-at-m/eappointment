<?php

namespace BO\Zmsapi\Exception\OverallCalendar;

use Exception;

class SlotConflict extends Exception
{
    protected $statusCode = 409;
    protected $message    = 'Die gewünschten Slots sind nicht verfügbar.';
}
