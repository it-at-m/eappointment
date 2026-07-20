<?php

namespace BO\Zmsbackend\Process\Exception;

/**
 * example class to generate an exception
 */
class TelephoneRequired extends \Exception
{
    protected $code = 400;

    protected $message = 'Es wurde keine Telefonnummer angegeben. Diese Aktion ist daher nicht möglich.';
}
