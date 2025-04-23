<?php

namespace BO\Zmsentities\Collection;

class TicketprinterList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Ticketprinter';

    public function getEntityByHash($hash)
    {
        $result = null;
        foreach ($this as $entity) {
            if ($entity->hash == $hash) {
                $result = $entity;
            }
        }
        return $result;
    }
}
