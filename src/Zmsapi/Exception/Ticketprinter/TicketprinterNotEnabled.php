<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class TicketprinterNotEnabled extends \Exception
{
    protected $code = 200;

    protected $message = 'Ticketprinter not enabled.';
}
