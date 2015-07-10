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
     * @return Collection
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

    /**
     * @return Collection
     */
    public function fetchCombinations($service_csv)
    {
        return $this->fetchList($this->fetchLocationCsv($service_csv));
    }

    /**
     * @return String
     */
    protected function fetchLocationCsv($service_csv)
    {
        $locationlist = $this->access()->fromLocation()->fetchList($service_csv);
        $locationIdList = array();
        foreach ($locationlist as $location) {
            $locationIdList[] = $location['id'];
        }
        return implode(',', $locationIdList);
    }

    /**
     * @return Collection
     */
    public function fetchFromCsv($service_csv)
    {
        $servicelist = new Collection();
        foreach (explode(',', $service_csv) as $service_id) {
            $service = $this->fetchId($service_id);
            if ($service) {
                $servicelist[$service_id] = $service;
            }
        }
        $servicelist->sortByName();
        return $servicelist;
    }
}
