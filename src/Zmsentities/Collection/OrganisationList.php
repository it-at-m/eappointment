<?php
namespace BO\Zmsentities\Collection;

class OrganisationList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Organisation';

    public function getByDepartmentId($departmentId)
    {
        $organisationList = new self();
        foreach ($this as $entity) {
            $organisation = new \BO\Zmsentities\Organisation($entity);
            if ($organisation->hasDepartment($departmentId)) {
                $organisationList->addEntity($organisation);
            }
        }
        return $organisationList;
    }

    public function withAccess(\BO\Zmsentities\Useraccount $useraccount)
    {
        $list = new static();
        foreach ($this as $organisation) {
            $organisation = clone $organisation;
            $organisation->departments = $organisation->getDepartmentList()->withAccess($useraccount);
            if ($organisation->hasAccess($useraccount)) {
                $list[] = $organisation;
            }
        }
        return $list;
    }

    public function sortByName()
    {
        parent::sortByName();
        foreach ($this as $organisation) {
            if ($organisation->departments instanceof DepartmentList) {
                $organisation->departments->sortByName();
            }
        }
        return $this;
    }
}
