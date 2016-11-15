<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterUnvalidButtonList extends \Exception
{
    protected $code = 500;

    protected $message = "One or more scopes or clusters in buttonlist does not match with organisation";
}
