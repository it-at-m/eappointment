<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class OAuthPreconditionFailed extends \Exception
{
    protected int $code = 412;

    protected string $message = 'A verfied email address is mandatory for login over open id connect';
}
