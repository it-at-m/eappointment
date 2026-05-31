<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class MatchingNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Provider does not match with given request or does not over request';
}
