<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Dayoff as Entity;

class DayOff extends Base
{

    public function readByDepartmentId($departmentId = 0)
    {
        $query = new Query\DayOff(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionDepartmentId($departmentId);
        return $this->fetchList($query, new Entity());
    }

    public function readByYear($year)
    {
        $query = new Query\DayOff(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionYear($year);
        return $this->fetchList($query, new Entity());
    }
}
