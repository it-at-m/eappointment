<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class TelephoneRequired extends \Exception
{
    protected int $code = 400;

    protected string $message = 'Es wurde keine Telefonnummer angegeben. Diese Aktion ist daher nicht möglich.';
}
