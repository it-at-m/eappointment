<?php

namespace BO\Zmsentities\Collection;

/**
 * @extends Base<\BO\Zmsentities\Ticketprinter>
 */
class TicketprinterList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Ticketprinter';

    public function getEntityByHash($hash): \BO\Zmsentities\Ticketprinter|null
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
