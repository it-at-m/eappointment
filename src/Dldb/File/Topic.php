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
     *
     * @return Collection
     */
    public function fetchList()
    {
        $topiclist = new Collection();
        foreach ($this->getItemList() as $item) {
            $service_csv = implode(',', $item->getServiceIds());
            if ($service_csv) {
                $servicelist = $this->access()
                        ->fromService($this->locale)
                        ->fetchFromCsv($service_csv);
                if (count($servicelist)) {
                    $topiclist[$item['id']] = $item;
                }
            }
        }
        return $topiclist->sortByName();
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

    /**
     * @return \BO\Dldb\Collection\Authorities
     */
    public function searchAll($querystring)
    {
        $topic = new Entity();
        $topic['relation']['locations'] = $this->access()->fromLocation()->searchList($querystring);
        $topic['relation']['services'] = $this->access()->fromService()->searchList($querystring);
        $topic['links'] = $this->access()->fromLink()->searchAll($querystring);
        //var_dump($topic);
        return $topic->getServiceLocationLinkList();
    }
}
