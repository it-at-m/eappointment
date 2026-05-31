<?php

namespace BO\Slim\Exception;

/**
 * example class to generate an exception
 */
class OAuthInvalid extends \Exception
{
    protected int $code = 401;

    protected string $message =
        'Something went wrong when trying to log in. Possibly the session had not expired yet. Try again. ';
}
