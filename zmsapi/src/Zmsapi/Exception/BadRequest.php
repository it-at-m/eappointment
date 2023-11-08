<?php

namespace BO\Zmsapi\Exception;

class BadRequest extends \Exception
{
    protected $code = 400;

    protected $message = 'The request body was empty or not having the right format.';
}
