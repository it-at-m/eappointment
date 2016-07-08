<?php
namespace BO\Zmsentities\Collection;

class OwnerList extends Base
{
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
        return $organisationList;
    }
}
