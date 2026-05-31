<?php

namespace BO\Zmsapi\Exception\Process;

class MoreThanAllowedQuantityPerService extends \Exception
{
    protected int $code = 400;

    protected string $message = 'The quantity of a service exceeds the maximum allowed quantity';
}
