<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

/**
 * example class to generate an exception
 */
class UnvalidButtonList extends \Exception
{
    protected $code = 428;

    protected $message = "Failed to get buttons of buttonlist, maybe one scope or cluster does not exist";
}
