<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterUnvalidButton extends \Exception
{
    protected int $code = 500;

    protected string $message = "Given type of button is not allowed, expected s, r, c or l";
}
