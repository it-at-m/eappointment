<?php

namespace BO\Zmsdb\Query;

class Preferences extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'preferences';

    const QUERY_SELECT_PROPERTY =
        'SELECT
                value
            FROM preferences
            WHERE
                entity = :entityName
                AND id = :entityId
                AND groupName = :groupName
                AND name = :name
            ';

    const QUERY_SELECT_TIMESTAMP =
        'SELECT
                updateTimestamp
            FROM preferences
            WHERE
                entity = :entityName
                AND id = :entityId
                AND groupName = :groupName
                AND name = :name
            ';

    const QUERY_REPLACE_PROPERTY =
        'REPLACE INTO preferences
            SET 
                entity = :entityName,
                id = :entityId,
                groupName = :groupName,
                name = :name,
                value = :value
            ';

    const QUERY_DELETE_PROPERTY =
        'DELETE FROM preferences
            WHERE
                entity = :entityName
                AND id = :entityId
                AND groupName = :groupName
                AND name = :name
            ';
}
