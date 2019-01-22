<?php

namespace BO\Zmsentities\Exception;

class ProviderListMissing extends \Exception
{
    protected $code = 404;

    protected $message = "At least one provider is required, please select a provider";
}
