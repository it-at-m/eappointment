<?php
namespace BO\Zmsentities\Collection;

class ClusterList extends Base
{
    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $entity) {
            if ($entityId == $entity->id) {
                return true;
            }
        }
        return false;
    }

    public function hasScope($scopeId)
    {
        foreach ($this as $entity) {
            foreach ($entity['scopes'] as $scope) {
                if ($scopeId == $scope->id) {
                    return true;
                }
            }
        }
        return false;
    }
}
