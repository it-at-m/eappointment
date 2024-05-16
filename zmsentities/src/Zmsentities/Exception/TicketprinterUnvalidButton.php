<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterUnvalidButton extends \Exception
{
    protected $code = 500;

    protected $message = "Given type of button is not allowed, expected s, r, c or l";
}
