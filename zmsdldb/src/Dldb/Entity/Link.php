<?php

/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

/**
  * Helper for topics export
  *
  */
class Link extends Base
{
    /**
     * return an ID for this entity
     *
     */
    public function getId()
    {
        return $this['link'];
    }
}
