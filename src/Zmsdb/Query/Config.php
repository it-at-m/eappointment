<?php

namespace BO\Zmsdb\Query;

class Config extends Base
{
    const QUERY_SELECT = '
        SELECT * FROM config
    ';

    const QUERY_UPDATE = '
        REPLACE INTO config (
            name,
            value,
            changeTimestamp
        )
        VALUES (
            ?,
            ?,
            ?
        )
    ';
}
