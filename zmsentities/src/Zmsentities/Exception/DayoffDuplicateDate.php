<?php

namespace BO\Zmsentities\Exception;

class DayoffDuplicateDate extends \Exception
{
    protected $code = 400;

    protected $message = 'A dayoff date must be unique';
}
