<?php

namespace BO\Zmsentities\Collection;

/**
 * @extends Base<\BO\Zmsentities\Useraccount>
 */
class UseraccountList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\UserAccount';

    public function withoutDublicates(): self
    {
        $collection = new self();
        foreach ($this as $useraccount) {
            if (! $collection->hasEntity($useraccount->getId())) {
                $collection->addEntity($useraccount);
            }
        }
        return $collection;
    }

    public function withAccessByWorkstation($workstation): self
    {
        $collection = new self();
        $departmentList = $workstation->getDepartmentList();
        foreach ($this as $useraccount) {
            if ($useraccount->hasPermissions(['department'])) {
                $accessedList = $departmentList;
            } else {
                $accessedList = $departmentList->withAccess($useraccount);
            }

            if ($accessedList->count()) {
                $collection->addEntity($useraccount);
            }
        }
        return $collection;
    }
}
