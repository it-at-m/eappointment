<?php

namespace BO\Zmsmessaging\Exception;

class InvalidMailAddress extends \Exception
{
    protected int $code = 422;

    protected string $message = 'No valid email exists';
}
