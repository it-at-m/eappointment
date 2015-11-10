<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Collection;

class SearchResults extends Base
{

    public function getNames()
    {
        $nameList = array();
        foreach ($this as $item) {
            $nameList[$item['id']] = $item['name'];
        }
        return $nameList;
    }

    public function toSearchResultData()
    {
        $list = new self();
        foreach ($this as $results) {
            foreach ($results as $item) {
                if (count($item)) {
                   $list[] = \BO\Dldb\Entity\SearchResult::create($item);
                }
            }
        }
        return $list;
    }

    public function addResults($data)
    {
        if ($data) {
            $this[] = $data;
            return $this;
        }
        return null;
    }
}
