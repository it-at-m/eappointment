<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class ProviderNotFound extends \Exception
{
    protected $code = 500;

    protected $message = 'Provider does not exists';
}
