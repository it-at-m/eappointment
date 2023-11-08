<?php

namespace BO\Zmsentities\Exception;

class ProcessListEmpty extends \Exception
{
    protected $code = 404;

    protected $message = 'There is no process available to resolve the Mail entity.';
}
