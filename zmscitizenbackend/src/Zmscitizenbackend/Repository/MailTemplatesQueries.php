<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

class MailTemplatesQueries
{
    public const string QUERY_SELECT_WITHOUT_PROVIDER = '
        SELECT
            mailtemplate.name AS name,
            mailtemplate.value AS value
        FROM mailtemplate
        WHERE mailtemplate.provider IS NULL
            OR mailtemplate.provider = ""
            OR mailtemplate.provider = "0"
    ';

    public const string QUERY_SELECT_BY_PROVIDER = '
        SELECT
            mailtemplate.name AS name,
            mailtemplate.value AS value
        FROM mailtemplate
        WHERE mailtemplate.provider = :providerId
    ';
}
