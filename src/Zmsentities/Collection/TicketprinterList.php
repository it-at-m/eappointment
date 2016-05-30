<?php
namespace BO\Zmsentities\Collection;

class TicketprinterList extends Base
{
    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Ticketprinter) {
            $this[] = clone $entity;
        }
        return $this;
    }
}
