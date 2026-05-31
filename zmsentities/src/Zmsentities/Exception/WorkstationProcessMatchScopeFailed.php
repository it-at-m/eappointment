<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class WorkstationProcessMatchScopeFailed extends \Exception
{
    protected int $code = 403;

    public $data;

    protected string $message = "Workstation is not allowed to edit the process,
        process scope does not match with workstation cluster/scope";
}
