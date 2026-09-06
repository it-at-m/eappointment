<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Entity\Link as Entity;
use BO\Zmsdldb\Collection\Links as Collection;

/**
 * Common methods shared by access classes
 *
 * @extends Base<Collection, Entity>
 */
class Link extends Base
{
    /**
     * @return void
     */
    #[\Override]
    public function loadData()
    {
        $data = $this->access()
            ->fromTopic()
            ->fetchList();
        /** @psalm-suppress InvalidArgument */
        $this->setItemList($this->parseData($data));
    }

    /**
     * @return Collection
     */
    #[\Override]
    protected function parseData(mixed $data)
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
     * @return Entity|false
     * @psalm-api
     */
    public function fetchPath(mixed $topic_path)
    {
        $topiclist = $this->fetchList();
        foreach ($topiclist as $topic) {
            if ($topic['path'] == $topic_path) {
                return $topic;
            }
        }
        return false;
    }

    /**
     * @psalm-api
     *
     * @return Collection
     */
    public function readSearchResultList(mixed $query)
    {
        $list = $this->getItemList();
        $result = new Collection();
        foreach ($list as $link) {
            if (false !== strpos($link['name'], $query)) {
                $result->append($link);
            }
        }
        return $result;
    }
}
