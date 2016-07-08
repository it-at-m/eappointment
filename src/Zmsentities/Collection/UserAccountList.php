<?php
namespace BO\Zmsentities\Collection;

class UserAccountList extends Base
{
    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
