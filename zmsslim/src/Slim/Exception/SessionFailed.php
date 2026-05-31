<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class SessionFailed extends \Exception
{
    protected int $code = 404;
}
