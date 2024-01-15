<?php

namespace BO\Zmsticketprinter\Exception;

class TemplateNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "Requested template could not be found";
}
