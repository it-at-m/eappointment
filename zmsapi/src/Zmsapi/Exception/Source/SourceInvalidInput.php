<?php

namespace BO\Zmsapi\Exception\Source;

class SourceInvalidInput extends \Exception
{
    protected $code = 404;

    protected $message = 'input data is not valid or source is not editable';
}
