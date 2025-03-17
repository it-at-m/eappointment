<?php

namespace BO\Zmsentities\Collection;

class ClusterList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Cluster';

    public function hasScope($scopeId)
    {
        foreach ($this as $entity) {
            foreach ($entity['scopes'] as $scope) {
                $scope = new \BO\Zmsentities\Scope($scope);
                if ($scopeId == $scope->id) {
                    return true;
                }
            }
        }
        return false;
    }

    public function withUniqueClusters()
    {
        $clusterList = new self();
        foreach ($this as $cluster) {
            if ($cluster && ! $clusterList->hasEntity($cluster->id)) {
                $clusterList->addEntity($cluster);
            }
        }
        return $clusterList;
    }

    public function sortByName()
    {
        parent::sortByName();
        foreach ($this as $cluster) {
            if ($cluster->scopes instanceof ScopeList) {
                $cluster->scopes->sortByName();
            }
        }
        return $this;
    }
}
