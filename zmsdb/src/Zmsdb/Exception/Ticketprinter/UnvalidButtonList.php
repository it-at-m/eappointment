<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class UnvalidButtonList extends \Exception
{
    protected int $code = 428;

    protected string $message = "Failed to get buttons of buttonlist, maybe one scope or cluster does not exist";
}
