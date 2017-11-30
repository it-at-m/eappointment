<?php

namespace BO\Zmsapi\Exception\Warehouse;

class ReportNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to read report';
}
