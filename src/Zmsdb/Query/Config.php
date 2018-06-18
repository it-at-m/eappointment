<?php

namespace BO\Zmsdb\Query;

class Config extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'config';

    const QUERY_SELECT = '
        SELECT * FROM config
    ';

    const QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM config
            WHERE name = ?
            ';

    const QUERY_REPLACE_PROPERTY =
        'REPLACE INTO config
            SET name  = :property, 
                value = :value
            ';


    public function addConditionName($itemName)
    {
        $this->query->where(self::TABLE .'.name', '=', $itemName);
        return $this;
    }
}
