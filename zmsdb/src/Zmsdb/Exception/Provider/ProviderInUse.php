<?php

namespace BO\Zmsdb\Exception\Provider;

class ProviderInUse extends \Exception
{
    protected $code = 409;

    protected $message = 'provider cannot be deleted because it is already in use';
}
