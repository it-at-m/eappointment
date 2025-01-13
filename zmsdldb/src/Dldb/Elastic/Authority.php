<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Elastic;

use BO\Dldb\Entity\Authority as Entity;
use BO\Dldb\Collection\Authorities as Collection;
use BO\Dldb\File\Authority as Base;

/**
 */
class Authority extends Base
{
    /**
     * fetch locations for a list of service and group by authority
     *
     * @return Collection\Authorities
     */
    public function fetchList($servicelist = false)
    {
        $boolquery = Helper::boolFilteredQuery();
        $boolquery->getFilter()->addMust(Helper::localeFilter($this->locale));
        $query = \Elastica\Query::create($boolquery);
        $limit = 1000;
        $filter = null;

        if ($servicelist && count($servicelist)) {
            $filter = new \Elastica\Filter\Terms('services.service', (array)$servicelist);
            $filter->setExecution('and');
            $query->setPostFilter($filter);
        }
        $query->addSort(['sort' => 'asc']);
        $resultList = $this
            ->access()
            ->getIndex()
            ->getType('location')
            ->search($query, $limit);
        return $this->fromLocationResults($resultList);
    }


    /**
     * Take an elasticsearch result and return a authority list
     *
     * @return Collection\Authorities
     */
    public function fromLocationResults($resultList)
    {
        $authorityList = new Collection();
        foreach ($resultList as $result) {
            $location = new \BO\Dldb\Entity\Location($result->getData());
            $authorityList->addLocation($location);
        }
        return $authorityList;
    }
}
