<?php

namespace BO\Zmsapi\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class UnvalidButtonList extends \Exception
{
    protected int $code = 428;

    protected string $message = "One or more scopes or clusters in buttonlist does not match with organisation";
}
