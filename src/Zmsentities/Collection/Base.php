<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Helper\Sorter;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
class Base extends \ArrayObject
{
    public function sortByName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(Sorter::toSortableString(ucfirst($a->name)), Sorter::toSortableString(ucfirst($b->name)));
        });
        return $this;
    }

    public function sortByTimeKey()
    {
        $this->uksort(function ($a, $b) {
            return ($a - $b);
        });
        return $this;
    }

    public function sortByCustomKey($key)
    {
        $this->uasort(function ($a, $b) use ($key) {
            return ($a[$key] - $b[$key]);
        });
        return $this;
    }

    public function __clone()
    {
        foreach ($this as $key => $property) {
            if (is_object($property)) {
                $this[$key] = clone $property;
            }
        }
    }

    public function hasEntity($primary)
    {
        foreach ($this as $entity) {
            if (isset($entity->{$entity::PRIMARY}) && $primary == $entity->{$entity::PRIMARY}) {
                return true;
            }
        }
        return false;
    }

    public function getEntity($primary)
    {
        $result = null;
        foreach ($this as $entity) {
            if (isset($entity->{$entity::PRIMARY}) && $primary == $entity->{$entity::PRIMARY}) {
                $result = $entity;
                break;
            }
        }
        return $result;
    }

    public function addEntity(\BO\Zmsentities\Schema\Entity $entity)
    {
        $this->offsetSet(null, $entity);
        return $this;
    }

    public function getIds()
    {
        $list = [];
        foreach ($this as $entity) {
            $list[] = $entity->id;
        }
        return $list;
    }

    public function getIdsCsv()
    {
        return implode(',', $this->getIds());
    }
}
