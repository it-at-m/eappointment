<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity
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
            $this->departments = new Collection\DepartmentList($this->departments);
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

    public function hasClusterScopesFromButtonList($buttonList)
    {
        $departmentList = new Collection\DepartmentList($this->departments);
        $scopeList = $departmentList->getUniqueScopeList();
        $clusterList = $departmentList->getUniqueClusterList();
        foreach ($buttonList as $button) {
            if ('scope' == $button['type'] && ! $scopeList->hasEntity($button['scope']['id'])) {
                throw new Exception\TicketprinterUnvalidButtonList();
            } elseif ('cluster' == $button['type'] && ! $clusterList->hasEntity($button['cluster']['id'])) {
                throw new Exception\TicketprinterUnvalidButtonList();
            }
        }
        return true;
    }

    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser'])
            || 0 < $this->getDepartmentList()->withAccess($useraccount)->count();
    }
}
