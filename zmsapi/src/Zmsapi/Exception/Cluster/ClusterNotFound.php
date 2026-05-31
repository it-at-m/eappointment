<?php

namespace BO\Zmsapi\Exception\Cluster;

/**
 * example class to generate an exception
 */
class ClusterNotFound extends \Exception
{
    protected int $code = 404;
    protected string $message = 'Failed to get Cluster by given ID';
}
