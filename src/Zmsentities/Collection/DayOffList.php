<?php
namespace BO\Zmsentities\Collection;

class DayOffList extends Base
{
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
        $result = null;
        foreach ($this as $entity) {
            if ($entity->name == $name) {
                $result = $entity;
            }
        }
        return $result;
    }

    public function toDateWithTimestamp()
    {
        $collection = new self();
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\DayOff($data);
            $dateTime = \DateTimeImmutable::createFromFormat('d.m.Y', $entity->date);
            $entity->date = $dateTime->modify('00:00:00')->getTimestamp();
            $collection->addEntity($entity);
        }
        return $collection;
    }
}
