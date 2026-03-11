<?php

namespace BO\Zmsadmin\Exception;

class NotAllowed extends \Exception
{
    protected $code = 403;

    protected $message = 'you are not allowed to access this service';

    /**
     * Data passed to TwigExceptionHandler for rendering.
     * Declared to avoid PHP 8.2 dynamic property deprecation notices.
     */
    public $templatedata = [];
}
