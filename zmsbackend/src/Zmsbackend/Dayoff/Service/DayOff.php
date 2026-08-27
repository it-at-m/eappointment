<?php

namespace BO\Zmsbackend\Dayoff\Service;

use BO\Zmsbackend\Application as App;
use BO\Zmsentities\Dayoff as Entity;
use BO\Zmsentities\Collection\DayoffList as Collection;

class DayOff extends \BO\Zmsbackend\Base
{
    /**
     * common DayOff like Xmas...
     *
     */
    public static $commonList = null;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function readByDepartmentId($departmentId = 0)
    {
        return $this->readCommon();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function readOnlyByDepartmentId($departmentId = 0, $disableCache = false)
    {
        return new Collection();
    }

    public function readCommon($disableCache = false)
    {
        $cacheKey = "dayOffs";

        if (!$disableCache && App::$cache) {
            $data = App::$cache->get($cacheKey);
            if (!empty($data)) {
                return $data;
            }
        }

        $dayOffList = new Collection();
        $query = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::SELECT);
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

        if (App::$cache) {
            App::$cache->set($cacheKey, $dayOffList);
        }

        return $dayOffList;
    }

   /**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
    public function readByScopeId($scopeId = 0, $disableCache = false)
    {
        return $this->readCommon();
    }

    public function readByYear($year): Collection
    {
        $dayOffList = new Collection();
        $query = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::SELECT);
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

    public function readCommonByYear($year): Collection
    {
        $dayOffList = new Collection();
        $query = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::SELECT);
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
     * @return \BO\Zmsentities\Collection\DayoffList
     */
    public function writeCommonDayoffsByYear($dayoffList, $year = null, bool $drop = true)
    {
        if ($drop && $year) {
            static::$commonList = null;
            $deleteQuery = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::DELETE);
            $deleteQuery
                ->addConditionYear($year)
                ->addConditionCommon();
            $this->deleteItem($deleteQuery);
        }
        $query = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::INSERT);
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

        $this->removeCache();

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
        $selectQuery = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addConditionDayoffDeleteInterval($deleteInSeconds);
        $statement = $this->fetchStatement($selectQuery);
        while ($dayoffData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dayoffData = (new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::SELECT))->postProcess($dayoffData);
            $entity = new Entity($dayoffData);
            $deleteQuery = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::DELETE);
            $date = (new \DateTimeImmutable())->setTimestamp($entity->date)->format('Y-m-d');
            $deleteQuery
                ->addConditionDate($date)
                ->addConditionName($entity->name);
            $this->deleteItem($deleteQuery);
        }

        $this->removeCache();
    }

    public function deleteEntity($itemId): bool
    {
        $query = new \BO\Zmsbackend\Dayoff\Repository\DayOff(\BO\Zmsbackend\Query\Base::DELETE);
        $query->addConditionDayOffId($itemId);

        $this->removeCache();

        return ($this->deleteItem($query));
    }

    /**
     * @return void
     */
    public function removeCache()
    {
        if (!App::$cache) {
            return;
        }

        if (App::$cache->has("dayOffs")) {
            App::$cache->delete("dayOffs");
        }
    }
}
