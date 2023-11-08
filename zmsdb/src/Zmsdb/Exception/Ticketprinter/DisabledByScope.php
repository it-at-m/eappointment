<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class DisabledByScope extends \Exception
{
    protected $code = 200;

    protected $message = "Ticketprinter disabled by scope";
}
