<?php

namespace BO\Zmsdb\Exception\Source;

class SourceInvalidInput extends \Exception
{
    protected $code = 401;

    protected $message = "The input values are invalid (must be editable true) or not complete.";
}
