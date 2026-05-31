<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class SessionDeleteFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Could not find any available session.';
}
