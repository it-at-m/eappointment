<?php

namespace BO\Zmsbackend\Matching\Exception;

/**
 *
 */
class ProviderNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Provider does not exists';
}
