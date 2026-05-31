<?php

namespace BO\Zmsticketprinter\Exception;

class HomeNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = "Home URL not found. Please contact to the administrator.";
}
