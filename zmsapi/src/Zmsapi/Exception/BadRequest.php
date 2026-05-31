<?php

namespace BO\Zmsapi\Exception;

class BadRequest extends \Exception
{
    protected int $code = 400;

    protected string $message = 'The request body was empty or not having the right format.';
}
