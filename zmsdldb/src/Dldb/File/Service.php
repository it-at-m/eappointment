<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use BO\Dldb\Entity\Service as Entity;
use BO\Dldb\Collection\Services as Collection;

/**
 * Common methods shared by access classes
 */
class Service extends Base
{
    protected function parseData($data)
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
     */
    public function searchAll($querystring, $service_csv = false, $location_csv = false)
    {
        $serviceList = $this->fetchList($location_csv);
        if ($querystring) {
            $serviceList = new Collection(array_filter((array) $serviceList, function ($item) use ($querystring) {
                $length = (3 < strlen($querystring)) ? strlen($querystring) : 3;
                $nameMatch = preg_match('/[' . $querystring . ']{' . $length . ',}/i', $item['name']);
                $keywordMatch = preg_match('/[' . $querystring . ']{' . $length . ',}/i', $item['meta']['keywords']);
                return ($nameMatch || $keywordMatch);
            }));
        }
        $serviceList = $serviceList->sortByName();
        return ($location_csv) ? $serviceList->containsLocation($location_csv) : $serviceList;
    }

    /**
     *
     * @return Collection
     */
    public function fetchList($location_csv = false)
    {
        #echo '<pre>' . print_r($this,1) . '</pre>';exit;
        $servicelist = $this->getItemList();
        if ($location_csv) {
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
     */
    public function fetchListRelated($service_id)
    {
        $service = $this->fetchId($service_id);
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
        return ($relatedList) ? $relatedList : new Collection();
    }

    /**
     *
     * @return Collection
     */
    public function fetchCombinations($service_csv)
    {
        return $this->fetchList($this->fetchLocationCsv($service_csv));
    }

    /**
     *
     * @return String
     */
    protected function fetchLocationCsv($service_csv)
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
    public function fetchFromCsv($service_csv)
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
     */
    public function fetchListFromTopic(\BO\Dldb\Entity\Topic $topic)
    {
        $itemlist = new Collection();
        $serviceIds = array();
        if ($topic) {
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
        }
        if (count($serviceIds)) {
            $servicelistCSV = implode(',', $serviceIds);
            $servicelist = $this->fetchFromCsv($servicelistCSV);
            return $servicelist;
        }
        return $itemlist->sortByName();
    }

    /**
     *
     * @return Collection
     */
    public function readSearchResultList($query, $service_csv = '')
    {
        $servicelist = $this->fetchCombinations($service_csv);
        $servicelist = new Collection(array_filter((array) $servicelist, function ($item) use ($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $servicelist;
    }
}
