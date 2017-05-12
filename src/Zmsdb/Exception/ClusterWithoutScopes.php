<?php

namespace BO\Zmsdb\Exception;

class ClusterWithoutScopes extends \Exception
{
    protected $code = 404;

    protected $message = "No scopes found for cluster";
}
