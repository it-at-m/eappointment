<?php

namespace BO\Zmsbackend\Calldisplay\Exception;

/**
 * example class to generate an exception
 */
class ScopeAndClusterNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Could not find any scope or cluster in calldisplay entity';
}
