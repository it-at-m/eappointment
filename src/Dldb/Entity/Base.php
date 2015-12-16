<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

class Base extends \ArrayObject
{
    /**
     * return a name for this entity
     *
     */
    public function getName()
    {
        return $this['name'];
    }
}
