<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class InvalidSession extends \Exception
{
    protected $code = 404;

    protected $message = 'Find valid session data failed.';
}
