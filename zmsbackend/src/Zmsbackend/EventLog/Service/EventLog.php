<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsbackend\EventLog\Service;

use BO\Zmsentities\EventLog as EventLogEntity;
use BO\Zmsentities\Collection\EventLogList as EventLogCollection;
use PDO;

class EventLog extends \BO\Zmsbackend\Base
{
    /**
     * @param string $name
     * @param string $reference
     * @return EventLogCollection
     */
    public function readByNameAndRef(string $name, string $reference): EventLogCollection
    {
        $query = new \BO\Zmsbackend\EventLog\Repository\EventLog(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addNameComparison($name)
            ->addReferenceComparison($reference);

        return $this->fetchList($query, new EventLogEntity(), new EventLogCollection());
    }

    /**
     * @param EventLogEntity $entity
     * @return bool
     */
    public function writeEntity(EventLogEntity $entity)
    {
        $query = new \BO\Zmsbackend\EventLog\Repository\EventLog(\BO\Zmsbackend\Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);

        return $this->writeItem($query);
    }

    public function deleteOutdated(): bool
    {
        $deleteQuery = new \BO\Zmsbackend\EventLog\Repository\EventLog(\BO\Zmsbackend\Query\Base::DELETE);
        $deleteQuery->addExpirationCondition();

        return $this->deleteItem($deleteQuery);
    }
}
