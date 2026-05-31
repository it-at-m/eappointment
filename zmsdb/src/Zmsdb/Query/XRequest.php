<?php

namespace BO\Zmsdb\Query;

class XRequest extends Base
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergeranliegen';

    public function addConditionProcessId($processId): static
    {
        $this->query->where('BuergerID', '=', $processId);
        return $this;
    }
}
