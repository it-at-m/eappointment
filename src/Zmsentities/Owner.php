<?php

namespace BO\Zmsentities;

class Owner extends Schema\Entity
{

    const PRIMARY = 'id';

    public static $schema = "owner.json";

    public function hasOrganisation($organisationId)
    {
        if (array_key_exists('organisations', $this)) {
            $organisationList = new Collection\OrganisationList($this->organisations);
            if ($organisationList->hasEntity($organisationId)) {
                return true;
            }
        }
        return false;
    }
}
