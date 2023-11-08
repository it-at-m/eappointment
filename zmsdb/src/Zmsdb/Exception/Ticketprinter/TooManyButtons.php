<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

class TooManyButtons extends \Exception
{
    protected $code = 404;

    protected $message = "Only 6 buttons are allowed";
}
