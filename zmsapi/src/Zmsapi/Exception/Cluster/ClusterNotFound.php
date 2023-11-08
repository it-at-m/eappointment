<?php

namespace BO\Zmsapi\Exception\Cluster;

/**
 * example class to generate an exception
 */
class ClusterNotFound extends \Exception
{
    protected $code = 404;
    protected $message = 'Failed to get Cluster by given ID';
}
