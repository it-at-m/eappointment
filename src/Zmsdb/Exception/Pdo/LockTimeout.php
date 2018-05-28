<?php

namespace BO\Zmsdb\Exception\Pdo;

class LockTimeout extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to lock database for updating';
}
