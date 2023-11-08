<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class UnvalidButtonList extends \Exception
{
    protected $code = 428;

    protected $message = "One or more scopes or clusters in buttonlist does not match with organisation";
}
