<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterHashNotValid extends \Exception
{
    protected int $code = 403;

    protected string $message = 'No valid hash existing';
}
