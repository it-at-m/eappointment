<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Office as Entity;
use \BO\Dldb\Collection\Offices as Collection;

/**
  * Common methods shared by access classes
  *
  */
class Office extends Base
{

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data']['office'] as $item) {
            $itemList[$item['path']] = new Entity($item);
            $itemList[$item['plural']] = $itemList[$item['path']];
        }
        return $itemList;
    }

    public function fetchList()
    {
        return $this->getItemList();
    }

    public function fetchPath($itemId)
    {
        return $this->fetchId($itemId);
    }
}
