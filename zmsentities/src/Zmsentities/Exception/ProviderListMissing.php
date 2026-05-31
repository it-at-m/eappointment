<?php

namespace BO\Zmsentities\Exception;

class ProviderListMissing extends \Exception
{
    protected int $code = 404;

    protected string $message = "At least one provider is required, please select a provider";
}
