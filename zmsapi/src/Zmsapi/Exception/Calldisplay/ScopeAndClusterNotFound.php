<?php

namespace BO\Zmsapi\Exception\Calldisplay;

/**
 * example class to generate an exception
 */
class ScopeAndClusterNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Could not find any scope or cluster in calldisplay entity';
}
