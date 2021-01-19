<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Dayoff as Entity;
use \BO\Zmsentities\Collection\DayoffList as Collection;

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
        $departmentDayoffList = $this->readOnlyByDepartmentId($departmentId);
        if (count($departmentDayoffList)) {
            foreach ($departmentDayoffList as $entity) {
                if ($entity instanceof Entity) {
                    $dayOffList->addEntity($entity);
                }
            }
        }
        return $dayOffList;
    }

    public function readOnlyByDepartmentId($departmentId = 0)
    {
        $dayOffList = new Collection();
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

    public function readCommonByYear($year)
    {
        $dayOffList = new Collection();
        $query = new Query\DayOff(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionCommon()
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

    /**
     * create dayoff preferences of a department
     *
     * @param
     *            dayoffList,
     *            year,
     *            drop
     *
     * @return Collection dayoffList
     */
    public function writeCommonDayoffsByYear($dayoffList, $year = null, $drop = true)
    {
        if ($drop && $year) {
            static::$commonList = null;
            $deleteQuery = new Query\DayOff(Query\Base::DELETE);
            $deleteQuery
                ->addConditionYear($year)
                ->addConditionCommon();
            $this->deleteItem($deleteQuery);
        }
        $query = new Query\DayOff(Query\Base::INSERT);
        foreach ($dayoffList as $dayoff) {
            $query->addValues(
                [
                    'behoerdenid' => 0, //all departments
                    'Feiertag' => $dayoff['name'],
                    'Datum' => (new \DateTimeImmutable())->setTimestamp($dayoff['date'])->format('Y-m-d')
                ]
            );
            $this->writeItem($query);
        }
        return ($year) ? $this->readCommonByYear($year) : $dayoffList;
    }

    /**
     * delete dayoff preferences by time interval
     *
     * @param
     *            deleteInSeconds
     *
     * @return boolean
     */
    public function deleteByTimeInterval($deleteInSeconds)
    {
        $selectQuery = new Query\DayOff(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addConditionDayoffDeleteInterval($deleteInSeconds);
        $statement = $this->fetchStatement($selectQuery);
        while ($dayoffData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dayoffData = (new Query\DayOff(Query\Base::SELECT))->postProcess($dayoffData);
            $entity = new Entity($dayoffData);
            if ($entity instanceof Entity) {
                $deleteQuery = new Query\DayOff(Query\Base::DELETE);
                $date = (new \DateTimeImmutable())->setTimestamp($entity->date)->format('Y-m-d');
                $deleteQuery
                    ->addConditionDate($date)
                    ->addConditionName($entity->name);
                $this->deleteItem($deleteQuery);
            }
        }
    }

    public function deleteEntity($itemId)
    {
        $query = new Query\DayOff(Query\Base::DELETE);
        $query->addConditionDayOffId($itemId);
        return ($this->deleteItem($query));
    }
}
