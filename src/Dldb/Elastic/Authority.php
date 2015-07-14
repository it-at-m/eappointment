<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Elastic;

use \BO\Dldb\Entity\Authority as Entity;
use \BO\Dldb\Collection\Authorities as Collection;
use \BO\Dldb\File\Authority as Base;

/**
  *
  */
class Authority extends Base
{


    /**
     * fetch locations for a list of service and group by authority
     * @return Collection\Authorities
     */
    public function fetchList(Array $servicelist = null)
    {
        if ($servicelist) {
            $filter = null;
            if (count($servicelist)) {
                $filter = new \Elastica\Filter\Terms('services.service', $servicelist);
                $filter->setExecution('and');
            }
            $query = \Elastica\Query::create($filter);
            $resultList = $this->access()->getIndex()->getType('location')->search($query, 10000);
            return $this->fromLocationResults($resultList);
        } else {
            return $this->getItemList();
        }
    }

    /**
     * Take an elasticsearch result and return a authority list
     *
     * @return Collection\Authorities
     */
    public function fromLocationResults($resultList, $sort = true)
    {
        $authoritylist = new Collection();
        foreach ($resultList as $result) {
            $location = new \BO\Dldb\Entity\Location($result->getData());
            $authoritylist->addLocation($location);
        }
        if ($sort) {
            $authoritylist->sortByName();
        }
        return $authoritylist;
    }
}
