<?php

namespace BO\Zmsbackend\Ticketprinter\Service;

use BO\Zmsentities\Ticketprinter as Entity;
use BO\Zmsentities\Collection\TicketprinterList as Collection;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Ticketprinter extends \BO\Zmsbackend\Base
{
    /**
     * read entity
     *
     * @param int|string $itemId
     *
     * @return Entity
     */
    public function readEntity($itemId)
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionTicketprinterId($itemId);
        $ticketprinter = $this->fetchOne($query, new Entity());
        $ticketprinter = $this->readWithContactData($ticketprinter);
        $ticketprinter->enabled = (1 == $ticketprinter->enabled);
        return $ticketprinter;
    }

    /**
     * read list of ticketprinters
     *
     * @return Collection
     */
    protected function readList($statement)
    {
        $ticketprinterList = new Collection();
        while ($entityData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($entityData);
            $ticketprinterList->addEntity($entity);
        }
        return $ticketprinterList;
    }

    /**
     * read Ticketprinter by Hash
     *
     * @param string $hash
     *
     * @return Entity
     */
    public function readByHash($hash)
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionHash($hash);
        $ticketprinter = $this->fetchOne($query, new Entity());
        $ticketprinter = $this->readWithContactData($ticketprinter);
        $ticketprinter->enabled = (1 == $ticketprinter->enabled);
        return $ticketprinter;
    }

    /**
     * read Ticketprinter by comma separated buttonlist
     *
     * @param \BO\Zmsentities\Ticketprinter $ticketprinter
     * @param \DateTimeInterface $now
     *
     * @return Entity
     */
    public function readByButtonList(Entity $ticketprinter, \DateTimeImmutable $now)
    {
        if (count($ticketprinter->buttons) > 6) {
            throw new \BO\Zmsbackend\Ticketprinter\Exception\TooManyButtons();
        }

        foreach ($ticketprinter->buttons as $key => $button) {
            if ('scope' == $button['type']) {
                $query = new \BO\Zmsbackend\Scope\Service\Scope();
                $scope = $query->readWithWorkstationCount($button['scope']['id'], $now);
                if (! $scope) {
                    throw new \BO\Zmsbackend\Ticketprinter\Exception\UnvalidButtonList();
                }
                $ticketprinter->buttons[$key]['scope'] = $scope;
                $ticketprinter->buttons[$key]['enabled'] = $query->readIsEnabled($scope->id, $now);
                $ticketprinter->buttons[$key]['name'] = $scope->getPreference('ticketprinter', 'buttonName');
            }

            if ('request' == $button['type']) {
                $scopeId = explode('-', $button['request']['id'])[0];
                $requestId = explode('-', $button['request']['id'])[1];
                $request = (new \BO\Zmsbackend\Request\Service\Request())->readEntity('dldb', $requestId);
                $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readWithWorkstationCount($scopeId, $now);

                if (! $request || ! $scope) {
                    throw new \BO\Zmsbackend\Ticketprinter\Exception\UnvalidButtonList();
                }
                $ticketprinter->buttons[$key]['scope'] = $scope;

                $ticketprinter->buttons[$key]['requestId'] = $requestId;
                $ticketprinter->buttons[$key]['enabled'] = (new \BO\Zmsbackend\Scope\Service\Scope())->readIsEnabled($scope->id, $now);
                $ticketprinter->buttons[$key]['name'] = $request->getProperty('name');
            }
        }
        $this->readExceptions($ticketprinter);
        $ticketprinter = $this->readWithContactData($ticketprinter);
        return $ticketprinter;
    }

    protected function readExceptions(Entity $ticketprinter)
    {
        $query = new \BO\Zmsbackend\Scope\Service\Scope();
        $scope = $this->readSingleScopeFromButtonList($ticketprinter);
        if ($scope && ! $query->readIsGivenNumberInContingent($scope['id'])) {
            throw new \BO\Zmsbackend\Scope\Exception\GivenNumberCountExceeded();
        }
    }

    protected function readWithContactData(Entity $entity)
    {
        $contact = new \BO\Zmsentities\Contact();

        /* cluster not allowed anymore as button (2018-01-30, Abnahme mit TE)
        if (1 == $entity->getClusterList()->count() && 0 == $entity->getScopeList()->count()) {
            $contact->name = $entity->getClusterList()->getFirst()->name;
        } elseif (0 == $entity->getClusterList()->count() && 1 == $entity->getScopeList()->count()) {
            $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($entity->getScopeList()->getFirst()->id);
            $contact->name = $department->name;
        }
        */

        if (1 == $entity->getScopeList()->count()) {
            $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($entity->getScopeList()->getFirst()->id);
            $contact->name = $department->name;
        }

        $entity->contact = $contact;
        return $entity;
    }

    public function readSingleScopeFromButtonList(Entity $ticketprinter)
    {
        $scope = null;
        if (1 == $ticketprinter->getScopeList()->count()) {
            $scope = $ticketprinter->getScopeList()->getFirst();
            $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($scope['id']);
        }
        /* cluster not allowed anymore as button (2018-01-30, Abnahme mit TE)
        elseif (1 == $ticketprinter->getClusterList()->count()) {
            $scopeList = $ticketprinter->getClusterList()->getFirst()->scopes;
            $scopeList = new \BO\Zmsentities\Collection\ScopeList($scopeList);
            if (1 == $scopeList->count()) {
                $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($scopeList->getFirst()['id']);
            }
        }
        */
        return $scope;
    }

    /**
     * write a cookie for ticketprinter
     *
     * @param int $organisationId
     * @param string $ticketprinterName
     *
     * @return Entity
     */
    public function writeEntityWithHash($organisationId, $ticketprinterName = '')
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::INSERT);
        $ticketprinter = (new Entity())->getHashWith($organisationId);
        $ticketprinter->name = $ticketprinterName;

        $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readEntity($organisationId);

        $values = $query->reverseEntityMapping($ticketprinter, $organisation->id);
        //get owner by organisation
        $owner = (new \BO\Zmsbackend\Owner\Service\Owner())->readByOrganisationId($organisationId);
        $values['kundenid'] = $owner->id;
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * write a ticketprinter
     *
     * @param \BO\Zmsentities\Ticketprinter $entity
     * @param int $organisationId
     *
     * @return Entity
     */
    public function writeEntity(Entity $entity, $organisationId)
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $organisationId);

        //get owner by organisation
        $owner = (new \BO\Zmsbackend\Owner\Service\Owner())->readByOrganisationId($organisationId);
        $values['kundenid'] = $owner->id;

        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * read list of ticketprinter by organisation
     *
     * @param int $organisationId
     *
     * @return Collection
     */
    public function readByOrganisationId($organisationId)
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionOrganisationId($organisationId);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement);
    }

    /**
    * remove an ticketprinter
    *
     * @param int|string $itemId
     *
     * @return Entity|null
    */
    public function deleteEntity($itemId)
    {
        $query =  new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::DELETE);
        $query->addConditionTicketprinterId($itemId);
        return $this->deleteItem($query);
    }

    public function readExpiredTicketprinterList($expirationDate)
    {
        $selectQuery = new \BO\Zmsbackend\Ticketprinter\Repository\Ticketprinter(\BO\Zmsbackend\Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addConditionDeleteInterval($expirationDate);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement);
    }
}
