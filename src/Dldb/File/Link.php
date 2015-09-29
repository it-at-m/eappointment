<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Link as Entity;
use \BO\Dldb\Collection\Links as Collection;

/**
  * Common methods shared by access classes
  *
  */
class Link extends Base
{

    public function __construct()
    {
    }

    public function loadData()
    {
        $data = $this->access()->fromTopic()->fetchList();
        $this->itemList = $this->parseData($data);
    }

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $topic) {
            foreach ($topic['links'] as $item) {
                $itemList[$item['link']] = new Entity($item);
            }
        }
        return $itemList;
    }

    /**
     * @return Collection
     */
    public function fetchList()
    {
        return $this->getItemList();
    }

    /**
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
}
