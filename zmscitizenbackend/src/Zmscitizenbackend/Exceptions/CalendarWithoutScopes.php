<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class CalendarWithoutScopes extends \Exception
{
    protected $code = 404;

    protected $message = 'No matching scopes found for given location(s)';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\CalendarWithoutScopes';
}
