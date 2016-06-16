<?php
namespace BO\Zmsentities\Collection;

class OrganisationList extends Base
{
    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
