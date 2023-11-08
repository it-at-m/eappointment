<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\File;

use \BO\Dldb\Entity\Location as Entity;
use \BO\Dldb\Collection\Locations as Collection;

/**
 * Common methods shared by access classes
 */
class Location extends Base
{
    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $item) {
            $location = new Entity($item);
            if ($location->isLocale($this->locale)) {
                $itemList[$item['id']] = $location;
            }
        }
        return $itemList;
    }

    /**
     *
     * @return Collection
     */
    public function fetchList($service_csv = false)
    {
        $locationlist = $this->getItemList();
        if ($service_csv) {
            $locationlist = new Collection(array_filter((array) $locationlist, function ($item) use ($service_csv) {
                $location = new Entity($item);
                return $location->containsService($service_csv);
            }));
        }
        return $locationlist;
    }

    /**
     *
     * @return Collection
     */
    public function fetchFromCsv($location_csv)
    {
        $locationlist = new Collection();
        foreach (explode(',', $location_csv) as $location_id) {
            $location = $this->fetchId($location_id);
            if ($location && $location->isLocale($this->locale)) {
                $locationlist[$location_id] = $location;
            }
        }
        return $locationlist;
    }

    /**
     *
     * @return Collection\Locations
     */
    public function readSearchResultList($query, $service_csv = '')
    {
        $locationlist = $this->fetchList($service_csv);
        $locationlist = new Collection(array_filter((array) $locationlist, function ($item) use ($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $this->access()
            ->fromAuthority()
            ->fromLocationResults($locationlist);
    }

    public function fetchListByOffice($office)
    {
        return $this->access()->fromAuthority()
        ->readListByOfficePath($office)
        ->removeEmptyAuthorities()
        ->sortByName();
    }
}
