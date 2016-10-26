<?php

namespace BO\Zmsapi\Exception\Provider;

/**
 * example class to generate an exception
 */
class TicketprinterNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Es konnte leider kein Ticketprinter gefunden werden.';
}
