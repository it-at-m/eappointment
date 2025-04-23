<?php

namespace BO\Zmsentities\Collection;

class DayoffList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Dayoff';

    public function hasEntityByDate($date)
    {
        return $this->getByDate($date) ? true : false;
    }

    public function getByDate($date)
    {
        $date = (new \BO\Zmsentities\Helper\DateTime($date))->format('Y-m-d');
        foreach ($this as $entity) {
            if ($entity->getDateTime()->format('Y-m-d') == $date) {
                return $entity;
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

    public function withTimestampFromDateformat($fromFormat = 'd.m.Y')
    {
        $collection = new self();
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\Dayoff($data); // if source is an array
            $entity = clone $entity;
            $entity->setTimestampFromDateformat($fromFormat);
            $collection->addEntity($entity);
        }
        return $collection;
    }

    public function testDatesInYear($year)
    {
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\Dayoff($data); // if source is an array
            $entityYear = (new \DateTimeImmutable())->setTimestamp($entity->date)->format('Y');
            if ($entityYear != $year) {
                throw new \BO\Zmsentities\Exception\DayoffWrongYear();
            }
        }
        return true;
    }

    public function withNew(DayoffList $dayoffList)
    {
        $list = new self();
        foreach ($dayoffList as $entity) {
            if (! $this->hasEntityByDate($entity->getDateTime())) {
                $list->addEntity($entity);
            }
        }
        return $list;
    }

    /**
     * Check if dayoff is newer than given time
     *
     * @return bool
     */
    public function isNewerThan(\DateTimeInterface $dateTime, $filterByAvailability = null, $now = null)
    {
        foreach ($this as $dayoff) {
            if ($dayoff->isNewerThan($dateTime, $filterByAvailability, $now)) {
                return true;
            }
        }
        return false;
    }
}
