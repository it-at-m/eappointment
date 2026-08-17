<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Collection;

use BO\Zmsdldb\Helper\Sorter;

/**
 * @extends \ArrayObject<int|string, \BO\Zmsdldb\Entity\Base>
 */
class Base extends \ArrayObject
{
    public function sortByName(): static
    {
        $itemList = clone $this;
        $itemList->uasort(function ($a, $b) {
            return strcmp(Sorter::toSortableString($a->getName()), Sorter::toSortableString($b->getName()));
        });
        return $itemList;
    }

    /**
     * @psalm-api
     */
    public function sortWithCollator($field = 'name', $locale = 'de'): static
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
