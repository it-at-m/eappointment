<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Availability as Entity;
use \BO\Zmsentities\Collection\AvailabilityList as Collection;

class Availability extends Base
{
    public static $cache = [];

    public function readEntity($availabilityId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "$availabilityId-$resolveReferences";
        if (!$disableCache && !array_key_exists($cacheKey, self::$cache)) {
            $query = new Query\Availability(Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionAvailabilityId($availabilityId);
            $availability = $this->fetchOne($query, new Entity());
            if ($availability->hasId() && $resolveReferences > 0) {
                $availability['scope'] = (new Scope())->readEntity($availability->scope['id'], $resolveReferences - 1);
            }
            self::$cache[$cacheKey] = $availability;
        }
        return clone self::$cache[$cacheKey];
    }

    public function readList($scopeId, $resolveReferences = 0, $reserveEntityIds = false)
    {
        $scope = new \BO\Zmsentities\Scope(['id' => $scopeId]);
        if (1 <= $resolveReferences) {
            $scope = (new Scope())->readEntity($scopeId, $resolveReferences - 1);
        }
        $collection = new Collection();
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity['scope'] = clone $scope;
                    $collection->addEntity($entity);
                }
            }
        }
        // TODO Remove after DB optimization
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping('openinghours')
            ->addConditionDoubleTypes()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($reserveEntityIds) {
                    // reserve an ID by creating a temporary entity
                    $tempAvailability = $this->writeEntity(new Entity([
                        'description' => '--temporary--',
                        'scope' => new \BO\Zmsentities\Scope([
                            'id' => 0,
                        ]),
                    ]));
                    $entity->id = $tempAvailability->id;
                }
                $entity['scope'] = clone $scope;
                $entity->workstationCount['intern'] = 0;
                $entity->workstationCount['callcenter'] = 0;
                $entity->workstationCount['public'] = 0;
                $collection->addEntity($entity);
            }
            if ($reserveEntityIds) {
                // This can produce deadlocks:
                $this->getReader()->exec(Query\Availability::TEMPORARY_DELETE);
            }
        }
        // End remove
        \BO\Zmsdb\Connection\Select::writeCommit();
        return $collection;
    }

    public function readOpeningHoursListByDate($scopeId, \DateTimeInterface $now, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping('openinghours')
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId)
            ->addConditionOpeningHours()
            ->addConditionDate($now);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    public function readByAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $query = new Query\Availability(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionScopeId($appointment->toProperty()->scope->id->get());
        $query->addConditionDate($appointment->toDateTime());
        $query->addConditionAppointmentTime($appointment->toDateTime());
        return $this->fetchOne($query, new Entity());
    }

    /**
     * write an availability
     *
     * @param
     * entityId
     *
     * @return lastInsertId()
     */
    public function writeEntity(\BO\Zmsentities\Availability $entity)
    {
        self::$cache = [];
        $entity->testValid();
        $query = new Query\Availability(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $entity->id = $this->getWriter()->lastInsertId();
        if (!$entity->id) {
            throw new \Exception("Could not insert $entity");
        }
        return $entity;
    }

    /**
     * update an availability
     *
     * @param
     * entityId
     *
     * @return Entity
     */
    public function updateEntity($entityId, \BO\Zmsentities\Availability $entity)
    {
        self::$cache = [];
        $entity->testValid();
        $query = new Query\Availability(Query\Base::UPDATE);
        $query->addConditionAvailabilityId($entityId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($entityId);
    }

    /**
     * remove an availability
     *
     * @param
     * availabilityId
     *
     * @return Resource Status
     */
    public function deleteEntity($availabilityId)
    {
        self::$cache = [];
        $query =  new Query\Availability(Query\Base::DELETE);
        $query->addConditionAvailabilityId($availabilityId);
        return $this->deleteItem($query);
    }
}
