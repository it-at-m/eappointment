<?php

namespace BO\Zmsdb\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterTooManyButtons extends \Exception
{
    protected $code = 500;

    protected $message = "Only 6 buttons are allowed";
}
