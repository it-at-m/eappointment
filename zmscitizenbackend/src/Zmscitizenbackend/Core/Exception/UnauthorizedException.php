<?php

namespace BO\Zmscitizenbackend\Core\Exception;

class UnauthorizedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("unauthorized", 403);
    }
}
