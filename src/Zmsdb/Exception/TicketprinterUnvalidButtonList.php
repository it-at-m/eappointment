<?php

namespace BO\Zmsdb\Exception;

/**
 * example class to generate an exception
 */
class TicketprinterUnvalidButtonList extends \Exception
{
    protected $code = 500;

    protected $message = "Failed to get buttons of buttonlist, maybe one scope or cluster does not exist";
}
