<?php

namespace BO\Zmsbackend\Owner\Exception;

class OwnerNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Owner id does not exists';
}
