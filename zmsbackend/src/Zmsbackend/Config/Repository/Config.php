<?php

namespace BO\Zmsbackend\Config\Repository;

class Config extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'config';

    const string QUERY_SELECT = '
        SELECT * FROM config
    ';

    const string QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM config
            WHERE name = ?
            ';

    const string QUERY_REPLACE_PROPERTY =
        'REPLACE INTO config
            SET name  = :property, 
                value = :value
            ';


    public function addConditionName($itemName): static
    {
        $this->query->where(self::TABLE . '.name', '=', $itemName);
        return $this;
    }
}
