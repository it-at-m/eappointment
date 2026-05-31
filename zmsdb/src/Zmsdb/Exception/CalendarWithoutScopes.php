<?php

namespace BO\Zmsdb\Exception;

class CalendarWithoutScopes extends \Exception
{
    protected int $code = 404;

    protected string $message = "No matching scopes found for given location(s)";
}
