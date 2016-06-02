<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Organisation as Entity;
use \BO\Zmsentities\Collection\OrganisationList as Collection;

class Organisation extends Base
{
    public function readEntity($itemId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOrganisationId($itemId);
        $organisation = $this->fetchOne($query, new Entity());
        $organisation['departments'] = (new Department())->readByOrganisationId($itemId, $resolveReferences);
        $organisation['ticketprinters'] = (new Ticketprinter())->readByOrganisationId($itemId, $resolveReferences);

        return $organisation;
    }

    public function readByOwnerId($ownerId, $resolveReferences = 0)
    {
        $organisationList = new Collection();
        $query = new Query\Organisation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOwnerId($ownerId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $organisation) {
                $entity = $this->readEntity($organisation->id, $resolveReferences - 1);
                $organisationList->addEntity($entity);
            }
        }
        return $organisationList;
    }

    public function readList($resolveReferences = 0)
    {
        $organisationList = new Collection();
        $query = new Query\Organisation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $organisation) {
                $organisation = new Entity($organisation);
                $organisationList->addEntity($organisation);
            }
        }
        return $organisationList;
    }

    /**
     * remove an organisatoin
     *
     * @param
     * itemId
     *
     * @return Resource Status
     */
    public function deleteEntity($itemId)
    {
        $query =  new Query\Organisation(Query\Base::DELETE);
        $query->addConditionOrganisationId($itemId);
        return $this->deleteItem($query);
    }
}
