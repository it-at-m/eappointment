<?php

namespace BO\Zmsapi\Exception\Warehouse;

class UnknownReportType extends \Exception
{
    protected $code = 404;

    protected $message = 'Type of report not known';
}
