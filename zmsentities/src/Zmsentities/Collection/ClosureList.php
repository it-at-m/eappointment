<?php

namespace BO\Zmsentities\Collection;

/**
 * @extends Base<\BO\Zmsentities\Closure>
 */
class ClosureList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Closure';

    public function hasEntityByDate($date): bool
    {
        return $this->getByDate($date) ? true : false;
    }

    public function getByDate($date): \BO\Zmsentities\Closure|false
    {
        $date = (new \BO\Zmsentities\Helper\DateTime($date))->format('Y-m-d');
        foreach ($this as $entity) {
            if ($entity->getDateTime()->format('Y-m-d') == $date) {
                return $entity;
            }
        }
        return false;
    }

    public function getEntityByName($name): \BO\Zmsentities\Closure|null
    {
        $result = null;
        foreach ($this as $entity) {
            if ($entity->name == $name) {
                $result = $entity;
            }
        }
        return $result;
    }

    public function withTimestampFromDateformat($fromFormat = 'd.m.Y'): self
    {
        $collection = new self();
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\Closure($data); // if source is an array
            $entity = clone $entity;
            $entity->setTimestampFromDateformat($fromFormat);
            $collection->addEntity($entity);
        }
        return $collection;
    }

    /**
     * @return true
     */
    public function testDatesInYear($year): bool
    {
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\Closure($data); // if source is an array
            $entityYear = (new \DateTimeImmutable())->setTimestamp($entity->date)->format('Y');
            if ($entityYear != $year) {
                throw new \BO\Zmsentities\Exception\DayoffWrongYear();
            }
        }
        return true;
    }

    public function withNew(ClosureList $closureList): self
    {
        $list = new self();
        foreach ($closureList as $entity) {
            if (! $this->hasEntityByDate($entity->getDateTime())) {
                $list->addEntity($entity);
            }
        }
        return $list;
    }

    /**
     * Check if closure is newer than given time
     *
     * @return bool
     */
    public function isNewerThan(\DateTimeInterface $dateTime, $filterByAvailability = null, $now = null)
    {
        foreach ($this as $closure) {
            if ($closure->isNewerThan($dateTime, $filterByAvailability, $now)) {
                return true;
            }
        }
        return false;
    }
}
