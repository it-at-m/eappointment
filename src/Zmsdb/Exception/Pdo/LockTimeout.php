<?php

namespace BO\Zmsdb\Exception\Pdo;

class LockTimeout extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to lock database for updating';
}
