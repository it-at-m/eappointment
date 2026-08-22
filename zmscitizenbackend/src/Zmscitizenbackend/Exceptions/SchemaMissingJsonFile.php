<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class SchemaMissingJsonFile extends \Exception
{
    protected $code = 500;
}
