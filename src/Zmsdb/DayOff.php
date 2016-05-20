<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\DayOff as Entity;
use \BO\Zmsentities\Collection\DayOffList as Collection;

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
        $dayOffList = new Collection();
        $query = new Query\DayOff(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionYear($year);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $dayOffList->addEntity($entity);
            }
        }
        return $dayOffList;
    }
}
