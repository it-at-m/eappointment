<?php

namespace BO\Zmsdb\Query;

class XRequest extends Base
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'citizen_requests';

    public function addConditionProcessId($processId)
    {
        $this->query->where('BuergerID', '=', $processId);
        return $this;
    }
}
