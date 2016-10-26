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
     *
     * @return Resource Entity
     */
    public function readEntity($itemId)
    {
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionTicketprinterId($itemId);
        $ticketprinter = $this->fetchOne($query, new Entity());
        return $ticketprinter;
    }

    /**
     * read list of ticketprinters
     *
     * @return Resource Collection
     */
    public function readList()
    {
        $ticketprinterList = new Collection();
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $ticketprinter) {
                if ($ticketprinter instanceof Entity) {
                    $ticketprinterList->addEntity($ticketprinter);
                }
            }
        }
        return $ticketprinterList;
    }

    /**
     * read Ticketprinter by Hash
     *
     * @param
     * hash
     *
     * @return Resource Entity
     */
    public function readByHash($hash)
    {
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionHash($hash);
        $ticketprinter = $this->fetchOne($query, new Entity());
        //\App::$log->error('ticketprinter by hash: ', [$ticketprinter]);
        return $ticketprinter;
    }

    /**
     * read Ticketprinter by comma separated buttonlist
     *
     * @param
     * ticketprinter Entity
     * now DateTime
     *
     * @return Resource Entity
     */
    public function readByButtonList(Entity $ticketprinter, \DateTimeImmutable $now)
    {
        $ticketprinter->toStructuredButtonList();
        foreach ($ticketprinter->buttons as $key => $button) {
            if ('scope' == $button['type']) {
                $query = new Scope();
                $ticketprinter->buttons[$key]['enabled'] = $query->readIsOpened($button['scope']['id'], $now);
                $ticketprinter->buttons[$key]['name'] = $query->readEntity($button['scope']['id'])->getName();
            } elseif ('cluster' == $button['type']) {
                $scopeList = (new Cluster())->readIsOpenedScopeList($button['cluster']['id'], $now);
                $ticketprinter->buttons[$key]['enabled'] = (count($scopeList)) ? true : false;
                $ticketprinter->buttons[$key]['name'] = $scopeList[0]->getName();
            }
        }
        return $ticketprinter;
    }

    /**
     * write a cookie for ticketprinter
     *
     * @param
     * organisationId
     *
     * @return Entity
     */
    public function writeCookie($organisationId)
    {
        $query = new Query\Ticketprinter(Query\Base::INSERT);
        $ticketprinter = new Entity();
        $hash = $ticketprinter->getHashWith($organisationId);
        $ticketprinter->hash = $hash;

        $organisation = (new Organisation())->readEntity($organisationId);
        $ticketprinter->enabled = (! $organisation->getPreference('ticketPrinterProtectionEnabled'));

        $values = $query->reverseEntityMapping($ticketprinter, $organisationId);
        $query->addValues($values);
        $this->writeItem($query);
        return $ticketprinter;
    }

    /**
     * write a ticketprinter
     *
     * @param
     * entity,
     * organisationId
     *
     * @return Entity
     */
    public function writeEntity(Entity $entity, $organisationId)
    {
        $query = new Query\Ticketprinter(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $organisationId);

        //get owner by organisation
        $owner = (new Owner())->readByOrganisationId($organisationId);
        $values['kundenid'] = $owner->id;

        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * read list of ticketprinter by organisation
     *
     * @param
     * organisationId
     *
     * @return Resource Collection
     */
    public function readByOrganisationId($organisationId)
    {
        $ticketprinterList = new Collection();
        $query = new Query\Ticketprinter(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionOrganisationId($organisationId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $ticketprinter) {
                if ($ticketprinter instanceof Entity) {
                    $ticketprinterList->addEntity($ticketprinter);
                }
            }
            return $ticketprinterList;
        }
    }

     /**
     * remove an ticketprinter
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
