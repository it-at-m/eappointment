<?php

namespace BO\Zmsdb\Exception\Calldisplay;

class ClusterNotFound extends \Exception
{
    protected $error = 500;

    protected $message = "Failed to find given cluster";
}
