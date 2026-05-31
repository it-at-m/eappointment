<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class InvalidSession extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Find valid session data failed.';
}
