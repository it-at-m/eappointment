<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Availability as Entity;
use \BO\Zmsentities\Collection\AvailabilityList as Collection;

/**
 * @SuppressWarnings(Public)
 */
class Availability extends Base implements Interfaces\ResolveReferences
{
    public static $cache = [];

    public function readEntity($availabilityId, $resolveReferences = 0, $preferCache = false)
    {
        $cacheKey = "$availabilityId-$resolveReferences";
        if (!$preferCache || !array_key_exists($cacheKey, self::$cache)) {
            $query = new Query\Availability(Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionAvailabilityId($availabilityId);
            $availability = $this->fetchOne($query, new Entity());
            $availability = $this->readResolvedReferences($availability, $resolveReferences);
            self::$cache[$cacheKey] = $availability;
        }
        return clone self::$cache[$cacheKey];
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        if (1 <= $resolveReferences && $entity->hasId()) {
            if (isset($entity->scope['id'])) {
                $entity['scope'] = (new Scope())->readEntity($entity->scope['id'], $resolveReferences - 1);
            }
        }
        return $entity;
    }

    public function readLock($availabilityId)
    {
        return $this->perform(Query\Availability::QUERY_GET_LOCK, ['availabilityId' => $availabilityId]);
    }

    public function readEntityDoubleTypes($availabilityId, $resolveReferences = 0)
    {
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping('openinghours')
            ->addResolvedReferences($resolveReferences)
            ->addConditionAvailabilityId($availabilityId)
            ->addConditionDoubleTypes();
        $availability = $this->fetchOne($query, new Entity());
        $availability = $this->readResolvedReferences($availability, $resolveReferences);
        return ($availability->hasId()) ? $availability : null;
    }

    public function readList(
        $scopeId,
        $resolveReferences = 0,
        \DateTimeInterface $startDate = null,
        \DateTimeInterface $endDate = null
    ) {
        $scope = new \BO\Zmsentities\Scope(['id' => $scopeId]);
        if (1 <= $resolveReferences) {
            $scope = (new Scope())->readEntity($scopeId, $resolveReferences - 1);
        }
        $collection = $this->readAvailabilityListByScope($scope, $resolveReferences, $startDate, $endDate);
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping('openinghours')
            ->addConditionOpeningHours()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        if ($startDate && $endDate) {
            $query->addConditionTimeframe($startDate, $endDate);
        } elseif ($startDate) {
            $query->addConditionSkipOld($startDate);
        }
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $entity['scope'] = clone $scope;
                $entity->workstationCount['intern'] = 0;
                $entity->workstationCount['callcenter'] = 0;
                $entity->workstationCount['public'] = 0;
                if ($entity['type'] == 'appointment') {
                    $entity['description'] = '';
                    $entity->id = '__spontan__'. $entity->id;
                    $entity['type'] = 'openinghours';
                }
                $entity['type'] = ($entity['type'] != 'appointment') ? 'openinghours' : $entity['type'];
                $collection->addEntity($entity);
            }
        }
        return $collection;
    }

    public function readAvailabilityListByScope(
        \BO\Zmsentities\Scope $scope,
        $resolveReferences = 0,
        \DateTimeImmutable $startDate = null,
        \DateTimeImmutable $endDate = null
    ) {
        $collection = new Collection();
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionAppointmentHours()
            ->addConditionScopeId($scope->id);
        if ($startDate && $endDate) {
            $query->addConditionTimeframe($startDate, $endDate);
        } elseif ($startDate) {
            $query->addConditionSkipOld($startDate);
        }
    
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity['scope'] = clone $scope;
                    //skip resolveReferences for using the scope given, TODO check resolvedLevel from scope
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    /* not in use
    public function readAvailabilityListByDate($scopeId, \DateTimeInterface $now, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId)
            ->addConditionAppointmentHours()
            ->addConditionDate($now);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }
    */

    /*
    ** Returns a list of availabilities with end date older than 4 weeks
    */
    public function readAvailabilityListBefore(\DateTimeImmutable $datetime, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionAppointmentHours()
            ->addConditionOnlyOld($datetime);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $collection->addEntity($entity);
                }
            }
        }
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $entity->type = 'openinghours';
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    public function readByAppointment(\BO\Zmsentities\Appointment $appointment, $resolveReferences = 0)
    {
        $query = new Query\Availability(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addResolvedReferences($resolveReferences);
        $query->addConditionScopeId($appointment->toProperty()->scope->id->get());
        $query->addConditionDate($appointment->toDateTime());
        $query->addConditionAppointmentTime($appointment->toDateTime());
        $entity = $this->fetchOne($query, new Entity());
        $entity->scope = $appointment->scope;
        $entity = $this->readResolvedReferences($entity, $resolveReferences);
        return $entity;
    }

    /**
     * write an availability
     *
     * @param
     * entityId
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Availability $entity, $resolveReferences = 0)
    {
        self::$cache = [];
        $entity->testValid();
        $query = new Query\Availability(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        if (!$this->writeItem($query)) {
            throw new Exception\Availability\AvailabilityCreateFailed();
        }
        $entity->id = $this->getWriter()->lastInsertId();
        return $this->readEntity($entity->id, $resolveReferences);
    }

    /**
     * update an availability
     *
     * @param
     * entityId
     *
     * @return Entity
     */
    public function updateEntity($entityId, \BO\Zmsentities\Availability $entity, $resolveReferences = 0)
    {
        self::$cache = [];
        $entity->testValid();
        $query = new Query\Availability(Query\Base::UPDATE);
        $query->addConditionAvailabilityId($entityId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($entityId, $resolveReferences);
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
