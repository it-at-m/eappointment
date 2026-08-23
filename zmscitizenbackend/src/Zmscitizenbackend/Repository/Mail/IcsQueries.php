<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Mail;

class IcsQueries
{
    public const string QUERY_SELECT_CONFIG = '
        SELECT
            config.name AS name,
            config.value AS value
        FROM config
    ';
}
