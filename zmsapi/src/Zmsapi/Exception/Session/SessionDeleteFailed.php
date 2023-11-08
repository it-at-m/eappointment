<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class SessionDeleteFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Could not find any available session.';
}
