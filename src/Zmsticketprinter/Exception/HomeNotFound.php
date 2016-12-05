<?php

namespace BO\Zmsticketprinter\Exception;

class HomeNotFound extends \Exception
{
    protected $error = 404;

    protected $message = "Home URL not found. Please contact to the administrator.";
}
