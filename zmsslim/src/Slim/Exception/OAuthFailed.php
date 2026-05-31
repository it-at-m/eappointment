<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class OAuthFailed extends \Exception
{
    protected int $code = 401;

    protected string $message = 'You are not allowed to access this client, please contact your system administrator.';
}
