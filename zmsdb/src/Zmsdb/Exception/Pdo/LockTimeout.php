<?php

namespace BO\Zmsdb\Exception\Pdo;

class LockTimeout extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Failed to lock database for updating';
}
