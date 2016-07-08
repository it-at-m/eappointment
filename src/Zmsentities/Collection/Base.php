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
        $itemList = clone $this;
        $itemList->uasort(function ($a, $b) {
            return strcmp(Sorter::toSortableString(ucfirst($a->name)), Sorter::toSortableString(ucfirst($b->name)));
        });
        return $itemList;
    }

    public function sortByTimeKey()
    {
        $itemList = clone $this;
        $itemList->uksort(function ($a, $b) {
            return ($a - $b);
        });
        return $itemList;
    }

    public function sortByCustomKey($key)
    {
        $itemList = clone $this;
        $itemList->uasort(function ($a, $b) use ($key) {
            return ($a[$key] - $b[$key]);
        });
        return $itemList;
    }

    public function __clone()
    {
        foreach ($this as $key => $property) {
            if (is_object($property)) {
                $this[$key] = clone $property;
            }
        }
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $entity) {
            if ($entityId == $entity->{$entity::PRIMARY}) {
                return true;
            }
        }
        return false;
    }
}
