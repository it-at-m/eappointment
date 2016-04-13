<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

class BaseController extends \ArrayObject
{

    public function sortByName()
    {
        $itemList = clone $this;
        $itemList->uasort(function ($a, $b) {
            return strcmp(Sorter::toSortableString($a->getName()), Sorter::toSortableString($b->getName()));
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
}
