<?php

namespace BO\Zmsdb\Exception\Provider;

class ProviderContactMissed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'contact data are required to write or update a provider';
}
