<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Ticketprinter as Entity;
use \BO\Zmsentities\Collection\TicketprinterList as Collection;

class Ticketprinter extends Base
{
    /**
    * read entity
    *
    * @param
    * itemId
    * resolveReferences
    *
    * @return Resource Entity
    */
    public function readEntity($itemId, $resolveReferences = 0)
    {
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionTicketprinterId($itemId);
        $ticketprinter = $this->fetchOne($query, new Entity());
        $ticketprinter['organisations'] = (new Organisation())->readByTicketprinterId($itemId, $resolveReferences);
        return $ticketprinter;
    }

     /**
     * read list of owners
     *
     * @param
     * resolveReferences
     *
     * @return Resource Collection
     */
    public function readList($resolveReferences = 0)
    {
        $ticketprinterList = new Collection();
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $ticketprinter) {
                $entity = $this->readEntity($ticketprinter['id'], $resolveReferences - 1);
                $ticketprinterList->addEntity($entity);
            }
        }
        return $ticketprinterList;
    }

    /**
     * read list of owners by organisation
     *
     * @param
     * resolveReferences
     *
     * @return Resource Collection
     */
    public function readByOrganisationId($organisationId, $resolveReferences = 0)
    {
        $ticketprinterList = new Collection();
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionOrganisationId($organisationId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $ticketprinter) {
                $entity = $this->readEntity($ticketprinter['id'], $resolveReferences - 1);
                $ticketprinterList->addEntity($entity);
            }
        }
        return $ticketprinterList;
    }

     /**
     * remove an owner
     *
     * @param
     * itemId
     *
     * @return Resource Status
     */
    public function deleteEntity($itemId)
    {
        $query =  new Query\Ticketprinter(Query\Base::DELETE);
        $query->addConditionTicketprinterId($itemId);
        return $this->deleteItem($query);
    }
}
