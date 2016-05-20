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
            'date' => self::expression('UNIX_TIMESTAMP(CONVERT_TZ(`dayOff`.`Datum`, "+00:00", @@global.time_zone))'),
            'name' => 'dayOff.Feiertag'
        ];
    }

    public function addConditionYear($year)
    {
        $this->query->where(self::expression('YEAR(`dayOff`.`Datum`)'), '=', $year);
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
