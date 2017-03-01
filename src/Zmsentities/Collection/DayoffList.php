<?php
namespace BO\Zmsentities\Collection;

class DayoffList extends Base
{
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

    public function hasDatesInYear($year)
    {
        foreach ($this as $data) {
            $entity = new \BO\Zmsentities\Dayoff($data); // if source is an array
            $entityYear = (new \DateTimeImmutable)->setTimestamp($entity->date)->format('Y');
            if ($entityYear != $year) {
                throw new \BO\Zmsentities\Exception\DayoffWrongYear();
            }
        }
        return true;
    }
}
