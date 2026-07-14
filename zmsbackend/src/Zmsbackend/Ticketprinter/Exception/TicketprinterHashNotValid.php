<?php

namespace BO\Zmsbackend\Ticketprinter\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterHashNotValid extends \Exception
{
    protected $code = 403;

    protected $message = 'No valid hash existing';
}
