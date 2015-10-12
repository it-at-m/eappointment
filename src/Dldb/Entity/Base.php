<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

class Base extends \ArrayObject
{

    /**
     * return an ID for this entity
     *
     */
    public function getId()
    {
        return $this['id'];
    }

    /**
     * return a name for this entity
     *
     */
    public function getName()
    {
        return $this['name'];
    }
}
