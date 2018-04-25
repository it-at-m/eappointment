<?php

namespace BO\Zmsdb\Exception\Pdo;

class DeadLockFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Deadlock found when trying to get lock; try restarting transaction';
}
