<?php
namespace BO\Zmsentities\Collection;

class OwnerList extends Base
{
    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Owner) {
            $this[] = clone $entity;
        }
        return $this;
    }
}
