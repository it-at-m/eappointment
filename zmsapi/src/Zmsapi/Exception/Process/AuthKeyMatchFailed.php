<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class AuthKeyMatchFailed extends \Exception
{
    protected int $code = 403;

    protected string $message = 'Der Absagecode ist nicht korrekt.';
}
