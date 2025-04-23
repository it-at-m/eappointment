<?php

namespace BO\Zmsentities\Collection;

class UseraccountList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\UserAccount';

    public function withRights($requiredRights)
    {
        $collection = new static();
        foreach ($this as $useraccount) {
            if ($useraccount->hasRights($requiredRights)) {
                $collection[] = clone $useraccount;
            }
        }
        return $collection;
    }

    public function withoutDublicates()
    {
        $collection = new self();
        foreach ($this as $useraccount) {
            if (! $collection->hasEntity($useraccount->getId())) {
                $collection->addEntity(clone $useraccount);
            }
        }
        return $collection;
    }

    public function withAccessByWorkstation($workstation)
    {
        $collection = new self();
        $departmentList = $workstation->getDepartmentList();
        foreach ($this as $useraccount) {
            $accessedList = $departmentList->withAccess($useraccount);

            if ($useraccount->hasRights(['department'])) {
                $accessedList = $departmentList;
            } else {
                $accessedList = $departmentList->withAccess($useraccount);
            }

            if ($accessedList->count()) {
                $collection->addEntity(clone $useraccount);
            }
        }
        return $collection;
    }
}
