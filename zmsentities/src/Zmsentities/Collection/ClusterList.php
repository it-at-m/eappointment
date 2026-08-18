<?php

namespace BO\Zmsentities\Collection;

/**
 * @extends Base<\BO\Zmsentities\Cluster>
 */
class ClusterList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Cluster';

    public function hasScope($scopeId): bool
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

    public function withUniqueClusters(): self
    {
        $clusterList = new self();
        foreach ($this as $cluster) {
            if ($cluster && ! $clusterList->hasEntity($cluster->id)) {
                $clusterList->addEntity($cluster);
            }
        }
        return $clusterList;
    }

    /**
     * @return static
     */
    #[\Override]
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
