<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

/**
  * Common methods shared by access classes
  *
  */
abstract class Base
{
    /**
     * @var \BO\Dldb\Collection\Base $itemList
     */
    protected $itemList = null;

    abstract function parseData($data);
}
