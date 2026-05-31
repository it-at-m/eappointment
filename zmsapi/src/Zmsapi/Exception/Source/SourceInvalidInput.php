<?php

namespace BO\Zmsapi\Exception\Source;

class SourceInvalidInput extends \Exception
{
    protected int $code = 404;

    protected string $message = 'input data is not valid or source is not editable';
}
