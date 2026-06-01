<?php

namespace BO\Zmsentities;

class Owner extends Schema\Entity implements Useraccount\AccessInterface
{
    public const PRIMARY = 'id';

    public static $schema = "owner.json";

    #[\Override]
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


    #[\Override]
    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser'])
            || 0 < $this->getOrganisationList()->withAccess($useraccount)->count();
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    #[\Override]
    public function withLessData()
    {
        $entity = clone $this;
        if ($entity->toProperty()->organisations->isAvailable()) {
            unset($entity['organisations']);
        }
        return $entity;
    }
}
