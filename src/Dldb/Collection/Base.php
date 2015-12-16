<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Collection;
use \BO\Dldb\Helper\Sorter;

use \BO\Dldb\Helper\Sorter;

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
}
