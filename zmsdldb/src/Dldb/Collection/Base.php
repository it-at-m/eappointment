<?php

/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

use BO\Dldb\Helper\Sorter;

class Base extends \ArrayObject
{
    public function sortByName()
    {
        $itemList = clone $this;
        $itemList->uasort(function ($a, $b) {
            return strcmp(Sorter::toSortableString($a->getName()), Sorter::toSortableString($b->getName()));
        });
        return $itemList;
    }

    public function sortWithCollator($field = 'name', $locale = 'de')
    {
        $collator = collator_create($locale);
        $collator->setStrength(\Collator::QUATERNARY);
        $collator->setAttribute(\Collator::QUATERNARY, \Collator::ON);
        $collator->setAttribute(\Collator::CASE_FIRST, \Collator::ON);
        $collator->setAttribute(\Collator::NUMERIC_COLLATION, \Collator::ON);

        $itemList = clone $this;
        $itemList->uasort(function ($itemA, $itemB) use ($collator, $field) {
            return collator_compare($collator, $itemA[$field], $itemB[$field]);
        });
        return $itemList;
    }
}
