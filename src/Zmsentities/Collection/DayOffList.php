<?php
namespace BO\Zmsentities\Collection;

class DayOffList extends Base
{

    public function addEntity($entity)
    {
        if ($entity instanceof \BO\Zmsentities\DayOff) {
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

    public function hasEntityByDate($date)
    {
        $date = new \BO\Zmsentities\Helper\DateTime($date);
        $date->modify('00:00:00');
        foreach ($this as $entity) {
            $entityDate = new \BO\Zmsentities\Helper\DateTime('@'. $entity->date);
            $entityDate = $entityDate->setTimezone($date->getTimezone());
            $entityDate = $entityDate->modify('00:00:00');
            if ($entityDate->format('U') == $date->format('U')) {
                return true;
            }
        }
        return false;
    }

    public function getEntityByName($name)
    {
        foreach ($this as $entity) {
            if ($entity->name == $name) {
                return $entity;
            }
        }
        return null;
    }
}
