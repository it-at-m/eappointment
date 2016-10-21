<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\DayOff as Entity;
use \BO\Zmsentities\Collection\DayOffList as Collection;

class DayOff extends Base
{
    /**
     * common DayOff like Xmas...
     *
     */
    public static $commonList = null;

    public function readByDepartmentId($departmentId = 0)
    {
        $dayOffList = $this->readCommon();
        $query = new Query\DayOff(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $dayOffList->addEntity($entity);
                }
            }
        }
        return $dayOffList;
    }

    public function readCommon()
    {
        if (static::$commonList === null) {
            $dayOffList = new Collection();
            $query = new Query\DayOff(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addConditionCommon();
            $result = $this->fetchList($query, new Entity());
            if (count($result)) {
                foreach ($result as $entity) {
                    if ($entity instanceof Entity) {
                        $dayOffList->addEntity($entity);
                    }
                }
            }
            static::$commonList = $dayOffList;
        }
        return clone static::$commonList;
    }

    public function readByScopeId($scopeId = 0)
    {
        $dayOffList = $this->readCommon();
        $query = new Query\DayOff(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $dayOffList->addEntity($entity);
                }
            }
        }
        return $dayOffList;
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
                if ($entity instanceof Entity) {
                    $dayOffList->addEntity($entity);
                }
            }
        }
        return $dayOffList;
    }
}
