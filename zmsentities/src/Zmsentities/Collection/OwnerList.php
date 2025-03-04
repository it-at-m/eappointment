<?php

namespace BO\Zmsentities\Collection;

class OwnerList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Owner';

    public function getOrganisationsByOwnerId($entityId)
    {
        $organisationList = new OrganisationList();
        foreach ($this as $entity) {
            if ($entityId == $entity->id) {
                foreach ($entity->organisations as $organisation) {
                    $organisation = new \BO\Zmsentities\Organisation($organisation);
                    $organisationList->addEntity($organisation);
                }
            }
        }
        return $organisationList->sortByName();
    }

    public function toDepartmentListByOrganisationName()
    {
        $list = array();
        foreach ($this as $entity) {
            $organisationList = $this->getOrganisationsByOwnerId($entity->id);
            foreach ($organisationList as $organisation) {
                $list[$entity->name][$organisation->name] = $organisation->departments;
            }
        }
        return $list;
    }

    public function withAccess(\BO\Zmsentities\Useraccount $useraccount)
    {
        $list = new static();
        foreach ($this as $owner) {
            $owner = clone $owner;
            $owner->organisations = $owner->getOrganisationList()->withAccess($useraccount);
            $owner->organisations->sortByName();
            if ($owner->hasAccess($useraccount)) {
                $list[] = $owner;
            }
        }
        return $list;
    }
}
