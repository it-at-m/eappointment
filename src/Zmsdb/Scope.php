<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;

class Scope extends Base
{
    public function readEntity($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        return $this->fetchOne($query, new Entity());
    }

    public function readByClusterId($clusterId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionClusterId($clusterId);
        return $this->fetchList($query, new Entity());
    }

    public function readByProviderId($providerId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        return $this->fetchList($query, new Entity());
    }

    public function readList($resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        return $this->fetchList($query, new Entity());
    }
}
