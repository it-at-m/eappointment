<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class DisabledByScope extends \Exception
{
    protected int $code = 200;

    protected string $message = "Ticketprinter disabled by scope";
}
