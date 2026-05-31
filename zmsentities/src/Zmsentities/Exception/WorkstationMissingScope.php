<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingScope extends \Exception
{
    protected int $code = 404;

    protected string $message = 'workstation has not an assigned scope';
}
