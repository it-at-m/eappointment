<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Availability as Entity;

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
            $availability['scope'] = (new Scope())->readEntity($availability['scope']['id'], 1);
            if (!isset($availability['department'])) {
                $availability['department'] = (new Department())
                    ->readEntity($availability['scope']['department']['id'], 1);
            }
            self::$cache[$availabilityId] = $availability;
        }
        return self::$cache[$availabilityId];
    }

    public function readList($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Availability(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        return $this->fetchList($query, new Entity());
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
        $query =  new Query\Availability(Query\Base::DELETE);
        $query->addConditionAvailabilityId($availabilityId);
        return $this->deleteItem($query);
    }
}
