<?php

/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

use BO\Dldb\Entity\SearchResult as Entity;

class SearchResults extends Base
{
    public function getNames()
    {
        $nameList = array();
        foreach ($this as $item) {
            $nameList[$item->getId()] = $item->getName();
        }
        return $nameList;
    }

    public function toSearchResultData()
    {
        $list = new self();
        foreach ($this as $results) {
            foreach ($results as $data) {
                if (count($data)) {
                    $item = Entity::create($data);
                    $list[] = $item;
                }
            }
            $list;
        }
        return $list;
    }

    public function addSearchResultsData($data)
    {
        if ($data) {
            $this[] = $data;
            return $this;
        }
        return null;
    }

    public function sortByType(array $order)
    {
        $list = new self();
        foreach ($order as $type) {
            foreach ($this as $item) {
                if ($item->getType() == $type) {
                    $list[] = $item;
                }
            }
        }
        return $list;
    }
}
