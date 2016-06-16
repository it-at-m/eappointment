<?php
namespace BO\Zmsentities\Collection;

class OwnerList extends Base
{
    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
