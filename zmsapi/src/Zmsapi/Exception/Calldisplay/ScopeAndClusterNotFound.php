<?php

namespace BO\Zmsapi\Exception\Calldisplay;

/**
 * example class to generate an exception
 */
class ScopeAndClusterNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Could not find any scope or cluster in calldisplay entity';
}
