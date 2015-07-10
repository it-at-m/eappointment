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
  *
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
     * @return Collection\Authorities
     */
    public function fetchList(Array $servicelist = array())
    {
        if (count($servicelist)) {
            $authoritylist = new Collection();
            foreach ($servicelist as $service_id) {
                $service = $this->access()->fromService()->fetchId($service_id);
                if ($service) {
                    foreach ($service['locations'] as $locationinfo) {
                        $location = $this->access()->fromLocation()->fetchId($locationinfo['location']);
                        if ($location) {
                            $authoritylist->addLocation($location);
                        }
                    }
                }
            }
            $authoritylist->sortByName();
        } else {
            $authoritylist = $this->getItemList();
        }
        return $authoritylist;
    }
}
