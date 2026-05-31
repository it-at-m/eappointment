<?php

namespace BO\Zmsticketprinter\Exception;

class ScopeNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = "To conseign a notification number, scope id is required";
}
