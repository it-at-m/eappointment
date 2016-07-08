<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
