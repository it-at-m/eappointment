<?php

namespace BO\Zmsbackend\Request\Repository;

class XRequest extends \BO\Zmsbackend\Query\Base
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergeranliegen';

    public function addConditionProcessId($processId)
    {
        $this->query->where('BuergerID', '=', $processId);
        return $this;
    }
}
