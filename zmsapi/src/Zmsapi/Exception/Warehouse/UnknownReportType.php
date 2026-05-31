<?php

namespace BO\Zmsapi\Exception\Warehouse;

class UnknownReportType extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Type of report not known';
}
