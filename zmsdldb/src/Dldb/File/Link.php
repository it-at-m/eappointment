<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use BO\Dldb\Entity\Link as Entity;
use BO\Dldb\Collection\Links as Collection;

/**
 * Common methods shared by access classes
 */
class Link extends Base
{
    public function loadData()
    {
        $data = $this->access()
            ->fromTopic()
            ->fetchList();
        $this->setItemList($this->parseData($data));
    }

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data as $topic) {
            foreach ($topic['links'] as $item) {
                $itemList[$item['link']] = new Entity($item);
            }
        }
        return $itemList;
    }

    /**
     *
     * @return Collection
     */
    public function fetchList()
    {
        return $this->getItemList();
    }

    /**
     *
     * @return Entity
     */
    public function fetchPath($topic_path)
    {
        $topiclist = $this->fetchList();
        foreach ($topiclist as $topic) {
            if ($topic['path'] == $topic_path) {
                return $topic;
            }
        }
        return false;
    }

    public function readSearchResultList($query)
    {
        $list = $this->getItemList();
        $result = new Collection();
        foreach ($list as $link) {
            if (false !== strpos($link['name'], $query)) {
                $result[] = $link;
            }
        }
        return $result;
    }
}
