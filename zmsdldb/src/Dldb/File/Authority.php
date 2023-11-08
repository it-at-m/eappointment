<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\File;

use \BO\Dldb\Entity\Authority as Entity;
use \BO\Dldb\Collection\Authorities as Collection;

/**
 * Common methods shared by access classes
 */
class Authority extends Base
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
     * fetch locations for a list of service and group by authority
     *
     * @return Collection
     */
    public function fetchList($servicelist = false)
    {
        $service_csv = implode(',', (array)$servicelist);
        $authoritylist = $this->getItemList()->removeLocations();
        $locationlist = $this->access()->fromLocation($this->locale)->fetchList($service_csv);
        if ($service_csv !== "") {
            $servicelist = $this->access()
                ->fromService($this->locale)
                ->fetchFromCsv($service_csv);
            $authoritylist = $authoritylist->toListWithAssociatedLocations($locationlist);
            $authoritylist = new Collection(array_filter((array) $authoritylist, function ($item) use ($servicelist) {
                $authority = new Entity($item);
                if ($authority->isInServiceList($servicelist)) {
                    return $authority;
                }
            }));
        } else {
            foreach ($locationlist as $location) {
                if ($location->isLocale($this->locale)) {
                    $authoritylist->addLocation($location);
                }
            }
        }
        return $authoritylist;
    }

    /**
     * Take an file search result and return a authority list
     *
     * @return Collection\Authorities
     */
    public function fromLocationResults($resultList)
    {
        $authorityList = new Collection();
        foreach ($resultList as $result) {
            $location = new \BO\Dldb\Entity\Location($result);
            $authorityList->addLocation($location);
        }
        return $authorityList;
    }

    /**
     *
     * @return Collection
     */
    public function readListByOfficePath($officepath)
    {
        $authoritylist = $this->fetchList();
        if ($officepath) {
            $authoritylist = $authoritylist->toListWithOfficePath($officepath);
        }
        return $authoritylist;
    }

    public function fetchSource()
    {
        return $this->getItemList();
    }
}
