<?php

namespace BO\Zmsapi\Exception\Config;

/**
 * example class to generate an exception
 */
class ConfigNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to get Config by given ID';
}
