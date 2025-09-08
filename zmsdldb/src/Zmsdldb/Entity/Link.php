<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Entity;

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
