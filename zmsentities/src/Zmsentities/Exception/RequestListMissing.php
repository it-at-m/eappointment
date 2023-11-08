<?php

namespace BO\Zmsentities\Exception;

class RequestListMissing extends \Exception
{
    protected $code = 404;

    protected $message = "At least one service is required, please select a service";
}
