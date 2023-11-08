<?php

namespace BO\Zmsdb\Exception\Pdo;

class DeadLockFound extends \Exception
{
    protected $code = 500;

    protected $message = 'Deadlock found when trying to get lock; try restarting transaction';
}
