<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Department as Entity;
use \BO\Zmsentities\Collection\DepartmentList as Collection;

class Department extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    public static $departmentCache = array();

    public function readEntity($departmentId, $resolveReferences = 0)
    {
        if (array_key_exists($departmentId, self::$departmentCache)) {
            return $departmentCache[$departmentId];
        }
        $query = new Query\Department(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $department = $this->fetchOne($query, new Entity());
        if (1 < $resolveReferences) {
            $department['scopes'] = (new Scope())->readByDepartmentId($departmentId, $resolveReferences);
        }
        $department['dayoff']  = (new DayOff())->readByDepartmentId($departmentId);
        $departmentCache[$departmentId] = $department;
        return $department;
    }

    public function readList($resolveReferences = 0)
    {
        $departmentList = new Collection();
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $department) {
                $department = $this->readEntity($department['id'], $resolveReferences);
                $departmentList->addDepartment($department);
            }
        }
        return $departmentList;
    }

    /**
     * remove a department
     *
     * @param
     * departmentId
     *
     * @return Resource Status
     */
    public function deleteEntity($departmentId)
    {
        $query =  new Query\Department(Query\Base::DELETE);
        $query->addConditionDepartmentId($departmentId);
        return $this->deleteItem($query);
    }
}
