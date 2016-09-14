<?php

namespace BO\Zmsdb\Query;

class DayOff extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'feiertage';

    public function getEntityMapping()
    {
        return [
            'date' => self::expression('FLOOR(UNIX_TIMESTAMP(`dayOff`.`Datum`))'),
            'name' => 'dayOff.Feiertag'
        ];
    }

    public function addConditionYear($year)
    {
        $this->query->where(self::expression('YEAR(`dayOff`.`Datum`)'), '=', $year);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->leftJoin(
            new Alias('standort', 'scope_dayoff'),
            'scope_dayoff.BehoerdenID',
            '=',
            'dayOff.BehoerdenID'
        );
        $this->query
            ->where('scope_dayoff.StandortID', '=', $scopeId)
            ->orWhere('dayOff.BehoerdenID', '=', 0);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query
            ->where('dayOff.BehoerdenID', '=', $departmentId)
            ->orWhere('dayOff.BehoerdenID', '=', 0);
        return $this;
    }
}
