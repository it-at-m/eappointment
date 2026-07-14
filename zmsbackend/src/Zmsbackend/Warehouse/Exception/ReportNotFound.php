<?php

namespace BO\Zmsbackend\Warehouse\Exception;

class ReportNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to read report';
}
