<?php

namespace BO\Zmsbackend\Process\Exception;

class MoreThanAllowedQuantityPerService extends \Exception
{
    protected $code = 400;

    protected $message = 'The quantity of a service exceeds the maximum allowed quantity';
}
