<?php

namespace BO\Zmscitizenapi\Exceptions;

use BO\Zmscitizenapi\Services\Core\LoggerService;

class InvalidAuthTokenException extends \RuntimeException
{
    public function __construct(string $errorCode, string $errorMessage = "")
    {
        parent::__construct("$errorCode: $errorMessage", 401);
        $logger = new LoggerService();
        $logger->logWarning($errorMessage);
    }
}
