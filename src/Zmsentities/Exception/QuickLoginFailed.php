<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class QuickLoginFailed extends \Exception
{
    protected $code = 403;

    protected $message = "Failed to login with quicklogin url";
}
