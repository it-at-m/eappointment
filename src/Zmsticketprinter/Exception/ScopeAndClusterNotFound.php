<?php

namespace BO\Zmsticketprinter\Exception;

class ScopeAndClusterNotFound extends \Exception
{
    protected $error = 500;

    protected $message = "To conseign a notification number, scope or cluster id is required";
}
