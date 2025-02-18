<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity implements Useraccount\AccessInterface
{
    public const PRIMARY = 'id';

    public static $schema = "organisation.json";

    public function getDefaults()
    {
        return [
            'departments' => new Collection\DepartmentList(),
            'name' => '',
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
    public function withLessData(array $keepArray = [])
    {
        $entity = clone $this;
        if (! in_array('preferences', $keepArray)) {
            unset($entity['preferences']);
        }
        if (! in_array('ticketprinters', $keepArray)) {
            unset($entity['ticketprinters']);
        }
        if (! in_array('departments', $keepArray)) {
            unset($entity['departments']);
        }
        return $entity;
    }
}
