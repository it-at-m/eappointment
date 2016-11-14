<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterNotEnabled extends \Exception
{
    protected $code = 500;

    protected $message = 'Ticketprinter not enabled.';
}
