<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Availability as Entity;

class Availability extends Base
{

    public function readEntity($availabilityId, $resolveReferences = 0)
    {
        $query = new Query\Availability(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionAvailabilityId($availabilityId);
        $availability = $this->fetchOne($query, new Entity());
        return $availability;
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
}
