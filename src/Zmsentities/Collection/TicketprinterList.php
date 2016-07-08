<?php
namespace BO\Zmsentities\Collection;

class TicketprinterList extends Base
{
    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
