<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingScope extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has not an assigned scope';
}
