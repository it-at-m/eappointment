<?php

namespace BO\Zmsdb\Exception\Provider;

class ProviderContactMissed extends \Exception
{
    protected $code = 404;

    protected $message = 'contact data are required to write or update a provider';
}
