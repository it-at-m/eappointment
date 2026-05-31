<?php

namespace BO\Zmsapi\Exception\Config;

/**
 * example class to generate an exception
 */
class ConfigAuthentificationFailed extends \Exception
{
    protected int $code = 401;

    protected string $message = 'Authentification failed - access to config not granted';
}
