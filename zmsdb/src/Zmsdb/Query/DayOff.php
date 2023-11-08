<?php

namespace BO\Zmsdb\Query;

class DayOff extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'feiertage';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getEntityMapping()
    {
        return [
            'id' => 'dayOff.FeiertagID',
            'date' => 'dayOff.Datum',
            'lastChange' => 'dayOff.updateTimestamp',
            'name' => 'dayOff.Feiertag'
        ];
    }

    public function addConditionYear($year)
    {
        $this->query->where(self::expression('YEAR(`dayOff`.`Datum`)'), '=', $year);
        return $this;
    }

    public function addConditionDate($date)
    {
        $this->query->where('dayOff`.`Datum', '=', $date);
        return $this;
    }

    public function addConditionName($name)
    {
        $this->query->where('dayOff`.`Feiertag', '=', $name);
        return $this;
    }

    public function addConditionCommon()
    {
        $this->query->where('dayOff.BehoerdenID', '=', 0);
        return $this;
    }

    public function addConditionDayOffId($itemId)
    {
        $this->query->where('dayOff.FeiertagID', '=', $itemId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->leftJoin(
            new Alias('standort', 'scope_dayoff'),
            'scope_dayoff.BehoerdenID',
            '=',
            'dayOff.BehoerdenID'
        );
        $this->query->where('scope_dayoff.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('dayOff.BehoerdenID', '=', $departmentId);
        return $this;
    }

    public function addConditionDayoffDeleteInterval($deleteInSeconds)
    {
        $this->query->where(
            self::expression(
                'UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`dayOff`.`Datum`)'
            ),
            '>=',
            $deleteInSeconds
        );
        return $this;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("date")] = (new \DateTime($data[$this->getPrefixed("date")]))->getTimestamp();
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
