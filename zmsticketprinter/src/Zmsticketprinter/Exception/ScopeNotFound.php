<?php

namespace BO\Zmsticketprinter\Exception;

class ScopeNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "To conseign a notification number, scope id is required";
}
