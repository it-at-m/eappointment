<?php

namespace BO\Zmsdb\Exception\Calldisplay;

class ScopeNotFound extends \Exception
{
    protected $error = 500;

    protected $message = "Failed to find given scope";
}
