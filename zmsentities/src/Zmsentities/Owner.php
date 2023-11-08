<?php

namespace BO\Zmsentities;

class Owner extends Schema\Entity implements Useraccount\AccessInterface
{
    const PRIMARY = 'id';

    public static $schema = "owner.json";

    public function getDefaults()
    {
        return [
            'organisations' => new Collection\OrganisationList(),
            'name' => '',
            ];
    }

    public function hasOrganisation($organisationId)
    {
        return $this->getOrganisationList()->hasEntity($organisationId);
    }

    public function getOrganisationList()
    {
        if (!$this->organisations instanceof Collection\OrganisationList) {
            $this->organisations = new Collection\OrganisationList($this->organisations);
            foreach ($this->organisations as $key => $organisation) {
                $this->organisations[$key] = new Organisation($organisation);
            }
        }
        return $this->organisations;
    }


    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser'])
            || 0 < $this->getOrganisationList()->withAccess($useraccount)->count();
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;
        if ($entity->toProperty()->organisations->isAvailable()) {
            unset($entity['organisations']);
        }
        return $entity;
    }
}
