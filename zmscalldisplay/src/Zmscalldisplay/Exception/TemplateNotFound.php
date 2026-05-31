<?php

namespace BO\Zmscalldisplay\Exception;

class TemplateNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = "Requested template could not be found";
}
