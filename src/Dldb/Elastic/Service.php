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
 * @SuppressWarnings(Coupling)
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
    public function searchAll($querystring, $service_csv = '', $location_csv = '')
    {
        $query = new \Elastica\Query();
        $locationsCsvByUser = false;
        if (! $location_csv) {
            $location_csv = $this->fetchLocationCsv($service_csv);
        } else {
            $locationsCsvByUser = true;
        }

        $boolquery = new \Elastica\Query\BoolQuery();
        $searchquery = new \Elastica\Query\QueryString();
        if ('' === trim($querystring)) {
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($querystring);
        }
        $searchquery->setFields([
            'name^9',
            'keywords^5'
        ]);

        $boolquery->addShould($searchquery);
        $filter = null;
        $filter = new \Elastica\Filter\BoolFilter();
        $filter->addMust(Helper::localeFilter($this->locale));
        if ($location_csv) {
            $filter->addMust(new \Elastica\Filter\Terms('locations.location', explode(',', $location_csv)));
        }
        $filteredQuery = new \Elastica\Query\Filtered($boolquery, $filter);
        $query->setQuery($filteredQuery);
        $query->addSort(['sort' => 'asc']);
        $resultList = $this->access()
        ->getIndex()
        ->getType('service')
        ->search($query, 1000);
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        if ($locationsCsvByUser) {
            $serviceList = $serviceList->containsLocation($location_csv);
        }
        return $serviceList;
    }

    /**
     * this function is similar to self::searchAll() but it might get different boosts in the future
     * additionally, a restriction by locale is missing
     *
     * @return Collection
     */
    public function readSearchResultList($query, $service_csv = '')
    {
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
        $boolquery->getQuery()->addShould($searchquery);
        $filter = null;
        if ($service_csv) {
            $filter = new \Elastica\Filter\Terms('services.service', explode(',', $service_csv));
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

    public function fetchServicesForCompilation($authoritys = [], $locations = [], $services = [])
    {
        $limit = 1000;

        $localeFilter = new \Elastica\Query\Term(array(
            'meta.locale' => $this->locale
        ));

        $boolquery = new \Elastica\Query\BoolQuery();
        $boolquery->addMust($localeFilter);

        if (!empty($authoritys)) {
            $authorityFilter = new \Elastica\Query\Terms('authorities.id', $authoritys);
            $boolquery->addMust($authorityFilter);
        }
        if (!empty($locations)) {
            $locationFilter = new \Elastica\Query\Terms('locations.location', $locations);
            $boolquery->addMust($locationFilter);
        }
        if (!empty($services)) {
            $serviceFilter = new \Elastica\Query\Terms('id', $services);
            $boolquery->addMust($serviceFilter);
        }

        $query = \Elastica\Query::create($boolquery);
        $query->addSort(['sort' => 'asc']);
        $resultList = $this
            ->access()
            ->getIndex()
            ->getType('service')
            ->search($query, $limit)
        ;
        $serviceList = new Collection();
        foreach ($resultList as $result) {
            $service = new Entity($result->getData());
            $serviceList[$service['id']] = $service;
        }
        return $serviceList;
    }
}
