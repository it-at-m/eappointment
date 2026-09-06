<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Collection;

use BO\Zmsdldb\Helper\Sorter;

/**
 * @template T of \BO\Zmsdldb\Entity\Base
 * @extends \ArrayObject<int|string, T>
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
    public function sortWithCollator(string $field = 'name', string $locale = 'de'): static
    {
        $collator = collator_create($locale);
        if ($collator === null) {
            throw new \RuntimeException('Could not create collator for locale ' . $locale);
        }
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
