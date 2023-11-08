<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class OAuthPreconditionFailed extends \Exception
{
    protected $code = 412;

    protected $message = 'A verfied email address is mandatory for login over open id connect';
}
