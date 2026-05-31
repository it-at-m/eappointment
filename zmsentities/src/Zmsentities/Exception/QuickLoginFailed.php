<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class QuickLoginFailed extends \Exception
{
    protected int $code = 403;

    protected string $message = "Failed to login with quicklogin url";
}
