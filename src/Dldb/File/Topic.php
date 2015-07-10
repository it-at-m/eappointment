<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use \BO\Dldb\Entity\Topic as Entity;

/**
  * Common methods shared by access classes
  *
  */
class Topic extends Base
{

    protected function parseData($data)
    {
        $itemList = array();
        foreach ($data['data'] as $item) {
            $itemList[$item['id']] = new Entity($item);
        }
        return $itemList;
    }

    /**
     * @return Collection\Topics
     */
    public function fetchList()
    {
        return $this->getItemList();
    }

    /**
     * @return Entity\Topic
     */
    public function fetchPath($topic_path)
    {
        $topiclist = $this->fetchTopicList();
        foreach ($topiclist as $topic) {
            if ($topic['path'] == $topic_path) {
                return $topic;
            }
        }
        return false;
    }
}
