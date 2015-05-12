<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Base extends \ArrayObject
{

    public function sortByName()
    {
        $this->uasort(function ($left, $right) {
            return strcmp($left['name'], $right['name']);
        });
    }
}
