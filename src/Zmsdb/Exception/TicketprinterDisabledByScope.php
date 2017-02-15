<?php

namespace BO\Zmsdb\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterDisabledByScope extends \Exception
{
    protected $code = 200;

    protected $message = "Ticketprinter disabled by scope";
}
