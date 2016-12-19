<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Availability as Entity;
use \BO\Zmsentities\Collection\AvailabilityList as Collection;

class Availability extends Base
{
    public static $cache = [];

    public function readEntity($availabilityId, $resolveReferences = 0, $disableCache = false)
    {
        if (!$disableCache && !array_key_exists($availabilityId, self::$cache)) {
            $query = new Query\Availability(Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionAvailabilityId($availabilityId);
            $availability = $this->fetchOne($query, new Entity());
            $availability['scope'] = (new Scope())->readEntity($availability['scope']['id'], $resolveReferences);
            self::$cache[$availabilityId] = $availability;
        }
        return self::$cache[$availabilityId];
    }

    public function readList($scopeId, $resolveReferences = 0)
    {
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
                    error_log($resolveReferences);
                    if (1 <= $resolveReferences) {
                        $entity['scope'] = (new Scope())
                            ->readEntity($entity['scope']['id'], $resolveReferences)
                            ->withLessData();
                    }
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
                if ($entity instanceof Entity) {
                    // reserve an ID by creating a temporary entity
                    $tempAvailability = $this->writeEntity(new Entity([
                        'description' => '--temporary--',
                        'scope' => new \BO\Zmsentities\Scope([
                            'id' => 0,
                            ]),
                    ]));
                    $entity->id = $tempAvailability->id;
                    $collection->addEntity($entity);
                }
            }
            $this->getReader()->exec(Query\Availability::TEMPORARY_DELETE);
        }
        // End remove
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
