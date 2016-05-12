<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base
{

    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Mail) {
            $this[] = clone $entity;
        }

        return $this;
    }

    public function hasEntity($itemId)
    {
        foreach ($this as $entity) {
            if ($entity->id == $itemId) {
                return true;
            }
        }
        return false;
    }
}
