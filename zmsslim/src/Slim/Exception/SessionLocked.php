<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class SessionLocked extends \Exception
{
    protected $code = 404;
}
