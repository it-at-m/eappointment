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
        $ticketprinter->enabled = (1 == $ticketprinter->enabled);
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
        //$ticketprinter->toStructuredButtonList();
        foreach ($ticketprinter->buttons as $key => $button) {
            if ($key < 6) {
                if ('scope' == $button['type']) {
                    $query = new Scope();
                    $scope = $query->readEntity($button['scope']['id']);
                    if (! $scope) {
                        throw new Exception\TicketprinterUnvalidButtonList();
                    }
                    $ticketprinter->buttons[$key]['scope'] = $scope;
                    $ticketprinter->buttons[$key]['enabled'] = $query->readIsOpened($scope->id, $now);
                    $ticketprinter->buttons[$key]['name'] = $scope->getPreference('ticketprinter', 'buttonName');
                } elseif ('cluster' == $button['type']) {
                    $query = new Cluster();
                    $cluster = $query->readEntity($button['cluster']['id']);
                    if (! $cluster) {
                        throw new Exception\TicketprinterUnvalidButtonList();
                    }
                    $scopeList = $query->readIsOpenedScopeList($cluster->id, $now);
                    $ticketprinter->buttons[$key]['cluster'] = $cluster;
                    $ticketprinter->buttons[$key]['enabled'] = (count($scopeList)) ? true : false;
                    $ticketprinter->buttons[$key]['name'] = $cluster->getName();
                }
            }
        }
        $this->readDisabledByScope($ticketprinter);
        return $ticketprinter;
    }

    protected function readDisabledByScope($ticketprinter)
    {
        if (1 == count($ticketprinter->buttons) && ! $ticketprinter->buttons[0]['enabled']) {
            $scope = (new Scope())->readEntity($ticketprinter->buttons[0]['scope']['id']);
            throw new Exception\TicketprinterDisabledByScope($scope->getPreference('ticketprinter', 'deactivatedText'));
        }
    }

    /**
     * write a cookie for ticketprinter
     *
     * @param
     * organisationId
     *
     * @return Entity
     */
    public function writeEntityWithHash($organisationId)
    {
        $query = new Query\Ticketprinter(Query\Base::INSERT);
        $ticketprinter = (new Entity())->getHashWith($organisationId);

        $organisation = (new Organisation())->readEntity($organisationId);
        $owner = (new Owner())->readByOrganisationId($organisationId);
        $ticketprinter->enabled = (! $organisation->getPreference('ticketPrinterProtectionEnabled'));

        $values = $query->reverseEntityMapping($ticketprinter, $organisation->id);
        //get owner by organisation
        $owner = (new Owner())->readByOrganisationId($organisationId);
        $values['kundenid'] = $owner->id;
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
        }
        return $ticketprinterList;
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
