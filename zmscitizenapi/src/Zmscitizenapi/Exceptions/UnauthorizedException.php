<?php

namespace BO\Zmscitizenapi\Exceptions;

class UnauthorizedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("unauthorized", 403);
    }
}
