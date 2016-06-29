<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $scope) {
            if ($entityId == $scope->id) {
                return true;
            }
        }
        return false;
    }
}
