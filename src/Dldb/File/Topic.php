<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Topic as Entity;
use \BO\Dldb\Collection\Topics as Collection;

/**
  * Common methods shared by access classes
  *
  */
class Topic extends Base
{

    protected function parseData($data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $item) {
            $itemList[$item['id']] = new Entity($item);
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
