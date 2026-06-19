<?php

namespace BO\Zmsbackend\Warehouse\Exception;

class UnknownReportType extends \Exception
{
    protected $code = 404;

    protected $message = 'Type of report not known';
}
