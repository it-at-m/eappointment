<?php

namespace BO\Zmsdb\Exception;

class CalendarWithoutScopes extends \Exception
{
    protected $code = 404;

    protected $message = "No matching scopes found for given location(s)";
}
