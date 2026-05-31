<?php

namespace BO\Zmsapi\Exception\Warehouse;

class ReportNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to read report';
}
