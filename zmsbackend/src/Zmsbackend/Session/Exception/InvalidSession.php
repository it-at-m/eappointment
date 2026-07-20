<?php

namespace BO\Zmsbackend\Session\Exception;

/**
 * example class to generate an exception
 */
class InvalidSession extends \Exception
{
    protected $code = 404;

    protected $message = 'Find valid session data failed.';
}
