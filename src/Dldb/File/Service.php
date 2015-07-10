<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Service as Entity;
use \BO\Dldb\Collection\Services as Collection;

/**
  * Common methods shared by access classes
  *
  */
class Service extends Base
{

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $item) {
            $itemList[$item['id']] = new Entity($item);
        }
        return $itemList;
    }

    /**
     * @return Collection\Services
     */
    public function fetchList($location_csv = false)
    {
        $servicelist = $this->getItemList();
        if ($location_csv) {
            $servicelist = new Collection(array_filter(
                (array)$servicelist,
                function ($item) use ($location_csv) {
                    $service = new Entity($item);
                    return $service->containsLocation($location_csv);
                }
            ));
        }
        return $servicelist;
    }
}
