<?php

namespace BO\Zmsdb\Exception\Source;

class SourceInvalidInput extends \Exception
{
    protected int $code = 401;

    protected string $message = "The input values are invalid (must be editable true) or not complete.";
}
