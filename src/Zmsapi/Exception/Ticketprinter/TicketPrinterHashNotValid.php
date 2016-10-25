<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class TicketPrinterHashNotValid extends \Exception
{
    protected $code = 403;

    protected $message = 'No valid hash found';
}
