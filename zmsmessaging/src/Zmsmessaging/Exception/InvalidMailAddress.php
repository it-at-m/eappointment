<?php

namespace BO\Zmsmessaging\Exception;

class InvalidMailAddress extends \Exception
{
    protected $code = 422;

    protected $message = 'No valid email exists';
}
