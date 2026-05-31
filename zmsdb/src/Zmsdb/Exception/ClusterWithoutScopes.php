<?php

namespace BO\Zmsdb\Exception;

class ClusterWithoutScopes extends \Exception
{
    protected int $code = 404;

    protected string $message = "No scopes found for cluster";
}
