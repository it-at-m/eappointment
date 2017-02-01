<?php

namespace BO\Zmsticketprinter\Exception;

class ScopeNotFound extends \Exception
{
    protected $error = 500;

    protected $message = "To conseign a notification number, scope id is required";
}
