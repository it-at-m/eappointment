<?php

namespace BO\Zmsapi\Exception\Config;

/**
 * example class to generate an exception
 */
class ConfigAuthentificationFailed extends \Exception
{
    protected $code = 401;

    protected $message = 'Authentification failed - access to config not granted';
}
