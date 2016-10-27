<?php

namespace BO\Zmsclient\Exception;

class ApiFailed extends \BO\Zmsclient\Exception
{
    protected $code = 500;
    public $template = 'bo/zmsclient/exception/apifailed';
}
