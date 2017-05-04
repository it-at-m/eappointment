<?php

namespace BO\Zmsdb\Exception\Calldisplay;

class ScopeNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "Failed to find given scope";
}
