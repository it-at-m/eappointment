<?php

namespace BO\Zmsentities\Exception;

class ProcessListEmpty extends \Exception
{
    protected int $code = 404;

    protected string $message = 'There is no process available to resolve the Mail entity.';
}
