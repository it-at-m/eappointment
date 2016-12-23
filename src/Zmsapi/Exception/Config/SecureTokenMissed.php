<?php

namespace BO\Zmsapi\Exception\Config;

/**
 * example class to generate an exception
 */
class SecureTokenMissed extends \Exception
{
    protected $code = 401;

    protected $message = 'Missed secure token - access not granted';
}
