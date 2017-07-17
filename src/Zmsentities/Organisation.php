<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity implements Useraccount\AccessInterface
{
    const PRIMARY = 'id';

    public static $schema = "organisation.json";

    public function getDefaults()
    {
        return [
            'departments' => new Collection\DepartmentList(),
        ];
    }

    public function hasDepartment($departmentId)
    {
        return $this->getDepartmentList()->hasEntity($departmentId);
    }

    public function getDepartmentList()
    {
        if (!$this->departments instanceof Collection\DepartmentList) {
            $this->departments = new Collection\DepartmentList((array)$this->departments);
            foreach ($this->departments as $key => $department) {
                $this->departments[$key] = new Department($department);
            }
        }
        return $this->departments;
    }

    public function getPreference($index)
    {
        return $this->toProperty()->preferences->$index->get();
    }

    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser'])
            || 0 < $this->getDepartmentList()->withAccess($useraccount)->count();
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;
        unset($entity['preferences']);
        unset($entity['ticketprinters']);
        unset($entity['departments']);
        return $entity;
    }
}
