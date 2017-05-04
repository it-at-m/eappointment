<?php

namespace BO\Zmsdb\Exception\Calldisplay;

class ClusterNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "Failed to find given cluster";
}
