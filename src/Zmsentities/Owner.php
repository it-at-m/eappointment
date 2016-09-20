<?php

namespace BO\Zmsentities;

class Owner extends Schema\Entity
{

    const PRIMARY = 'id';

    public static $schema = "owner.json";

    public function hasOrganisation($organisationId)
    {
        $organisationList = new Collection\OrganisationList();
        foreach ($this->toProperty()->organisations->get() as $organisation) {
            $organisationList->addEntity(new Organisation($organisation));
        }
        return ($organisationList->hasEntity($organisationId)) ? true : false;
    }
}
