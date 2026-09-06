<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Entity\Office as Entity;
use BO\Zmsdldb\Collection\Offices as Collection;

/**
  * Common methods shared by access classes
  *
  * @extends Base<Collection, Entity>
  */
class Office extends Base
{
    /**
     * @return Collection
     */
    #[\Override]
    protected function parseData(mixed $data)
    {
        $itemList = new Collection();
        foreach ($data['data']['office'] as $item) {
            $itemList[$item['path']] = new Entity($item);
            $itemList[$item['plural']] = $itemList[$item['path']];
        }
        return $itemList;
    }

    public function fetchList(): \BO\Zmsdldb\Collection\Base
    {
        return $this->getItemList();
    }

    /** @psalm-api */
    public function fetchPath(mixed $itemId): \BO\Zmsdldb\Entity\Base|false
    {
        return $this->fetchId($itemId);
    }
}
