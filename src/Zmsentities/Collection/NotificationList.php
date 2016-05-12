<?php
namespace BO\Zmsentities\Collection;

class NotificationList extends Base
{

    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Notification) {
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
