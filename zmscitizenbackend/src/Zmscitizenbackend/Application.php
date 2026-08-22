<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend;

class Application
{
    public const string IDENTIFIER = 'zms';
    public const string MODULE_NAME = 'zmscitizenbackend';

    /**
     * Optional PSR-3 logger used by collection models when skipping invalid items.
     */
    public static mixed $log = null;
}
