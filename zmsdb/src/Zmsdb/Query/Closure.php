<?php

namespace BO\Zmsdb\Query;

use DateTime;

class Closure extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'closures';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getEntityMapping()
    {
        return [
            'id' => 'closure.id',
            'year' => 'closure.year',
            'month' => 'closure.month',
            'day' => 'closure.day',
            'lastChange' => 'closure.updateTimestamp'
        ];
    }

    public function addConditionDate(DateTime $date)
    {
        $this->query->where('closure.year', '=', $date->format('Y'));
        $this->query->where('closure.month', '=', $date->format('m'));
        $this->query->where('closure.day', '=', $date->format('d'));
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('closure.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionId($id)
    {
        $this->query->where('closure.id', '=', $id);
        return $this;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
