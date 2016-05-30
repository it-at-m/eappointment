<?php
namespace BO\Zmsentities\Collection;

class OrganisationList extends Base
{
    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Organisation) {
            $this[] = clone $entity;
        }
        return $this;
    }
}
