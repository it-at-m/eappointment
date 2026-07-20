<?php

namespace BO\Zmsbackend\Source\Exception;

class SourceInvalidInput extends \Exception
{
    protected $code = 401;

    protected $message = "The input values are invalid (must be editable true) or not complete.";
}
