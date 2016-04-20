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
            'date' => self::expression('UNIX_TIMESTAMP(`dayoff`.`Datum`)'),
            'name' => 'dayoff.Feiertag'
        ];
    }

    public function addConditionYear($year)
    {
        $this->query->where(self::expression('YEAR(`dayoff`.`Datum`)'), '=', $year);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query
            ->where('dayoff.BehoerdenID', '=', $departmentId)
            ->orWhere('dayoff.BehoerdenID', '=', 0);
        return $this;
    }
}
