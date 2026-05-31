<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Es konnte leider kein Ticketprinter gefunden werden.';
}
