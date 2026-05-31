<?php

namespace BO\Zmsclient\Exception;

class ApiFailed extends \BO\Zmsclient\Exception
{
    protected int $code = 500;
    public string $template = 'bo/zmsclient/exception/apifailed';
    public mixed $templatedata = null;
}
