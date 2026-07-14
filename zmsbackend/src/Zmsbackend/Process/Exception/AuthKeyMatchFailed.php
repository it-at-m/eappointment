<?php

namespace BO\Zmsbackend\Process\Exception;

/**
 * example class to generate an exception
 */
class AuthKeyMatchFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'Der Absagecode ist nicht korrekt.';
}
