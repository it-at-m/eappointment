<?php

namespace BO\Zmsbackend\Request\Repository;

class XRequest extends \BO\Zmsbackend\Query\Base
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'buergeranliegen';

    public function addConditionProcessId($processId): static
    {
        $this->query->where('BuergerID', '=', $processId);
        return $this;
    }
}
