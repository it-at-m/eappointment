<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class ProviderNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Provider does not exists';
}
