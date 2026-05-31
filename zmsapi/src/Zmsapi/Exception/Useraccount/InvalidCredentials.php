<?php

namespace BO\Zmsapi\Exception\Useraccount;

class InvalidCredentials extends \Exception
{
    protected int $code = 401;

    protected string $message = 'account credentials are invalid';

    /**
     * @var array
     */
    public array $data = [];
}
