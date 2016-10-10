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

    public function addConditionName($itemName)
    {
        $this->query->where(self::TABLE .'.name', '=', $itemName);
        return $this;
    }
}
