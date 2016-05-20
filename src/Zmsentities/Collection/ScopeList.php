<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{

    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Scope) {
            $this[] = clone $entity;
        }
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $scopeId) {
            if ($entityId == $scopeId) {
                return true;
            }
        }
        return false;
    }
}
