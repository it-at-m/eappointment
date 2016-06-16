<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
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
