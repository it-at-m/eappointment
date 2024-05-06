<?php

namespace BO\Zmsdb\Query;

class MailTemplates extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailtemplates';

    const QUERY_SELECT = '
        SELECT * FROM mailtemplates
    ';

    const QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM mailtemplates
            WHERE name = ?
            ';

    const QUERY_REPLACE_PROPERTY =
        'REPLACE INTO mailtemplates
            SET name  = :property, 
                value = :value
            ';


    public function addConditionName($itemName)
    {
        $this->query->where(self::TABLE .'.name', '=', $itemName);
        return $this;
    }
}
