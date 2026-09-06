<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Entity\Service as Entity;
use BO\Zmsdldb\Collection\Services as Collection;

/**
 * Common methods shared by access classes
 *
 * @extends Base<Collection, Entity>
 */
class Service extends Base
{
    /**
     * @return Collection
     */
    #[\Override]
    protected function parseData(mixed $data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $item) {
            $service = new Entity($item);
            if ($service->isLocale($this->locale)) {
                $itemList[$item['id']] = $service;
            }
        }
        return $itemList;
    }

    /**
     *
     * @SuppressWarnings(Param)
     * @return Collection
     * @psalm-api
     */
    public function searchAll(mixed $querystring, bool|string $service_csv = false, bool|string $location_csv = false)
    {
        $serviceList = new Collection($this->fetchList($location_csv)->getArrayCopy());
        if ($querystring) {
            $serviceList = new Collection(array_filter((array) $serviceList, function ($item) use ($querystring) {
                $length = (3 < strlen($querystring)) ? strlen($querystring) : 3;
                $nameMatch = preg_match('/[' . $querystring . ']{' . $length . ',}/i', $item['name']);
                $keywordMatch = preg_match('/[' . $querystring . ']{' . $length . ',}/i', $item['meta']['keywords']);
                return ($nameMatch || $keywordMatch);
            }));
        }
        /** @var array<int|string, Entity> $sortedItems */
        $sortedItems = $serviceList->sortByName()->getArrayCopy();
        $serviceList = new Collection($sortedItems);
        if (!is_string($location_csv)) {
            return $serviceList;
        }
        return $serviceList->containsLocation($location_csv);
    }

    /**
     * @return Collection
     *
     * @param bool|string $location_csv
     */
    public function fetchList(bool|string $location_csv = false)
    {
        #echo '<pre>' . print_r($this,1) . '</pre>';exit;
        $servicelist = $this->getItemList();
        if (is_string($location_csv) && $location_csv !== '') {
            $servicelist = new Collection(array_filter((array) $servicelist, function ($item) use ($location_csv) {
                $service = new Entity($item);
                return $service->containsLocation($location_csv);
            }));
        }
        return $servicelist;
    }

    /**
     *
     * @return Collection
     * @psalm-api
     */
    public function fetchListRelated(mixed $service_id)
    {
        $service = $this->fetchId($service_id);
        if ($service === false) {
            return new Collection();
        }
        $serviceList = $this->getItemList();

        $relatedList = new Collection(
            array_filter(
                (array) $serviceList,
                function ($item) use ($service) {
                    $leikaIdentItem = substr(strval($item['leika']), 0, 11);
                    $leikaIdentService = substr(strval($service['leika']), 0, 11);
                    return ($leikaIdentItem == $leikaIdentService && $item['id'] != $service['id']);
                }
            )
        );
        return $relatedList;
    }

    /**
     *
     * @return Collection
     */
    public function fetchCombinations(mixed $service_csv)
    {
        return $this->fetchList($this->fetchLocationCsv($service_csv));
    }

    /**
     *
     * @return string
     */
    protected function fetchLocationCsv(mixed $service_csv)
    {
        $locationlist = $this->access()
            ->fromLocation()
            ->fetchList($service_csv);
        $locationIdList = array();
        foreach ($locationlist as $location) {
            $locationIdList[] = $location['id'];
        }
        return implode(',', $locationIdList);
    }

    /**
     *
     * @return Collection
     */
    public function fetchFromCsv(string $service_csv)
    {
        $servicelist = new Collection();
        foreach (explode(',', $service_csv) as $service_id) {
            $service = $this->fetchId($service_id);
            if ($service && $service->isLocale($this->locale)) {
                $servicelist[$service_id] = $service;
            }
        }
        return $servicelist;
    }

    /**
     * Return services by topic
     * If topic is root, include sub-services
     * root_topic in realations not usable because of multiple roots for one service
     *
     * @return Collection
     * @psalm-api
     */
    public function fetchListFromTopic(\BO\Zmsdldb\Entity\Topic $topic)
    {
        $itemlist = new Collection();
        $serviceIds = $topic->getServiceIds();
        if ($topic['relation']['navi'] && isset($topic['relation']['childs'])) {
            foreach ($topic['relation']['childs'] as $child) {
                $childtopic = $this->access()
                    ->fromTopic()
                    ->fetchPath($child['path']);
                if ($childtopic) {
                    $serviceIds = array_merge($serviceIds, $childtopic->getServiceIds());
                }
            }
        }
        if (count($serviceIds)) {
            $servicelistCSV = implode(',', $serviceIds);
            $servicelist = $this->fetchFromCsv($servicelistCSV);
            return $servicelist;
        }
        /** @var Collection $sorted */
        $sorted = $itemlist->sortByName();
        return $sorted;
    }

    /**
     *
     * @return Collection
     * @psalm-api
     */
    public function readSearchResultList(mixed $query, string $service_csv = '')
    {
        $servicelist = $this->fetchCombinations($service_csv);
        $servicelist = new Collection(array_filter((array) $servicelist, function ($item) use ($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $servicelist;
    }
}
