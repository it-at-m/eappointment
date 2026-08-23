<?php

namespace BO\Zmscitizenbackend\Core\Exception;

class InvalidAuthTokenException extends \RuntimeException
{
    public function __construct(string $errorCode, string $errorMessage = "")
    {
        parent::__construct("$errorCode: $errorMessage", 401);
    }
}
