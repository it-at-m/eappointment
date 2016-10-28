<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Es konnte leider kein Ticketprinter gefunden werden.';
}
