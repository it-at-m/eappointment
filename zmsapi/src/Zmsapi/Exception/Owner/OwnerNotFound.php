<?php

namespace BO\Zmsapi\Exception\Owner;

class OwnerNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Owner id does not exists';
}
