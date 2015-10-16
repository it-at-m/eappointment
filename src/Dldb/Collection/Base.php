<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Collection;

use \BO\Dldb\Helper\Sorter;

class Base extends \ArrayObject
{

    public function sortByName()
    {
        $itemlist = clone $this;
        $classname = get_class($this);
        $list = new $classname();
        foreach ($itemlist as $item) {
            $list[Sorter::toSortableString($item->getName()) .' - '. $item->getId()] = $item;
        }
        $list->ksort();
        return $list;
    }
}
