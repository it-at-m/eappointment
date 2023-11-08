<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Borough as Entity;
use \BO\Dldb\Collection\Boroughs as Collection;

/**
  * Common methods shared by access classes
  *
  */
class Borough extends Base
{

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data']['boroughs'] as $item) {
            $itemList[$item['id']] = new Entity($item);
        }
        return $itemList;
    }
}
