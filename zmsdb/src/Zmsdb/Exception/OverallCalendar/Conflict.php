<?php

namespace BO\Zmsdb\Exception\OverallCalendar;

class Conflict extends \RuntimeException
{
    protected $message = 'Gewünschte Zeitfenster sind bereits belegt.';
}
