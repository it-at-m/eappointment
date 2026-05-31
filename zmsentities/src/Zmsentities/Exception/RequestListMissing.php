<?php

namespace BO\Zmsentities\Exception;

class RequestListMissing extends \Exception
{
    protected int $code = 404;

    protected string $message = "At least one service is required, please select a service";
}
