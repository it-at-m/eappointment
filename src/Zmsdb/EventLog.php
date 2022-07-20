<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb;

use \BO\Zmsentities\Schema\Entity as BaseEntity;
use \BO\Zmsentities\EventLog as EventLogEntity;
use \BO\Zmsentities\Collection\Base as Collection;

class EventLog extends Base
{
    /**
     * @param string $name
     * @param string $reference
     * @return Collection
     */
    public function readByNameAndRef(string $name, string $reference): Collection
    {
        $query = new Query\EventLog(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addNameComparison($name)
            ->addReferenceComparison($reference);

        return $this->fetchCollection($query, new EventLogEntity());
    }

    public function writeEntity(BaseEntity $entity)
    {
        $query = new Query\EventLog(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        return $this->writeItem($query);
    }

    public function deleteOutdated()
    {
        $deleteQuery = new Query\EventLog(Query\Base::DELETE);
        $deleteQuery->addExpirationCondition();
        $this->deleteItem($deleteQuery);
    }
}