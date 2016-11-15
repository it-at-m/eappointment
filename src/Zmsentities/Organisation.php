<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "organisation.json";

    public function hasDepartment($departmentId)
    {
        $hasDepartment = false;
        foreach ($this->departments as $department) {
            if ($departmentId == $department['id']) {
                $hasDepartment = true;
            }
        }
        return $hasDepartment;
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
}
