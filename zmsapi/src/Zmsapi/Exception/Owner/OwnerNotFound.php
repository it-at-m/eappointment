<?php

namespace BO\Zmsapi\Exception\Owner;

class OwnerNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Owner id does not exists';
}
