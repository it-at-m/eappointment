<?php

namespace BO\Zmsdb\Exception\Pdo;

class DeadLockFound extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Deadlock found when trying to get lock; try restarting transaction';
}
