<?php

namespace BO\Zmsdb\Exception\Ticketprinter;

class TooManyButtons extends \Exception
{
    protected int $code = 404;

    protected string $message = "Only 6 buttons are allowed";
}
