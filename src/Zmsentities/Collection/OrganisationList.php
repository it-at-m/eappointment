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
            if ($useraccount->hasRights(['department'])) {
                $organisation->departments = $organisation->getDepartmentList();
            } else {
                $organisation->departments = $organisation->getDepartmentList()->withAccess($useraccount);
            }
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

    public function withMatchingDepartments(DepartmentList $departmentList)
    {
        $list = new static();
        foreach ($this as $organisation) {
            $entity = clone $organisation;
            $entity->departments = new DepartmentList();
            $departmentMatchList = $organisation->getDepartmentList();
            foreach ($departmentList as $department) {
                if ($departmentMatchList->hasEntity($department->id)) {
                    $entity->departments->addEntity($department);
                }
            }
            if ($entity->departments->count()) {
                $list->addEntity($entity);
            }
        }
        return $list;
    }
}
