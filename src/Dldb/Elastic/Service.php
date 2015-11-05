<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Elastic;

use \BO\Dldb\Entity\Service as Entity;
use \BO\Dldb\Collection\Services as Collection;
use \BO\Dldb\File\Service as Base;

/**
 */
class Service extends Base
{

    /**
     *
     * @return Entity\Service
     */
    public function fetchId($service_id)
    {
        if ($service_id) {
            $query = Helper::boolFilteredQuery();
            $filter = new \Elastica\Filter\Ids();
            $filter->setIds($this->locale . $service_id);
            $query->getFilter()->addMust($filter);
            $result = $this->access()
                ->getIndex()
                ->getType('service')
                ->search($query);
            if ($result->count() == 1) {
                $locationList = $result->getResults();
                return new Entity($locationList[0]->getData());
            }
        }
        return false;
    }

    /**
     *
     * @return Collection\Services
     */
    public function fetchList($location_csv = false)
    {
        $boolquery = Helper::boolFilteredQuery();
        $boolquery->getFilter()->addMust(Helper::localeFilter($this->locale));
        $query = \Elastica\Query::create($boolquery);
        if ($location_csv) {
            $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
            $filter->setExecution('and');
            $query->setPostFilter($filter);
        }
        $resultList = $this->access()
            ->getIndex()
            ->getType('service')
            ->search($query, 10000);
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     *
     * @return Collection\Services
     */
    public function fetchFromCsv($service_csv)
    {
        $query = Helper::boolFilteredQuery();
        $filter = new \Elastica\Filter\Ids();
        $ids = explode(',', $service_csv);
        $ids = array_map(function ($value) {
            return $this->locale . $value;
        }, $ids);
        $filter->setIds($ids);
        $query->getFilter()->addMust($filter);
        $resultList = $this->access()
            ->getIndex()
            ->getType('service')
            ->search($query, 10000);
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     *
     * @return Collection\Services
     */
    public function searchAll($query, $service_csv = '', $location_csv = '')
    {
        if (! $location_csv) {
            $location_csv = $this->fetchLocationCsv($service_csv);
        }
        // $boolquery = new \Elastica\Query\Bool();
        $boolquery = Helper::boolFilteredQuery();
        $boolquery->getFilter()->addMust(Helper::localeFilter($this->locale));
        $searchquery = new \Elastica\Query\QueryString();
        if ('' === trim($query)) {
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($query);
        }
        $searchquery->setFields([
            'name^9',
            'keywords^5'
        ]);
        $searchquery->setLowercaseExpandedTerms(false);
        $boolquery->getQuery()->addShould($searchquery);
        $filter = null;
        if ($location_csv) {
            $filter = new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv));
        }
        $query = new \Elastica\Query\Filtered($boolquery, $filter);
        $resultList = $this->access()
            ->getIndex()
            ->getType('service')
            ->search($query, 1000);
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }

    /**
     * this function is similar to self::searchAll() but it might get different boosts in the future
     * additionally, a restriction by locale is missing
     *
     * @return Collection
     */
    public function readSearchResultList($query)
    {
        $boolquery = Helper::boolFilteredQuery();
        $searchquery = new \Elastica\Query\QueryString();
        if ('' === trim($query)) {
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($query);
        }
        $searchquery->setFields([
            'name^9',
            'keywords^5'
        ]);
        $boolquery->getQuery()->addShould($searchquery);
        $filter = null;
        $query = new \Elastica\Query\Filtered($boolquery, $filter);
        $resultList = $this->access()
            ->getIndex()
            ->getType('service')
            ->search($query, 1000);
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }
}
