<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterNotEnabled extends \Exception
{
    protected int $code = 200;

    protected string $message = 'Ticketprinter not enabled.';
}
