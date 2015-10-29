<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\Entity;

/**
 * Helper for topics export
 */
class Topic extends Base
{

    public function getServiceIds()
    {
        $serviceIds = array();
        foreach ($this['relation']['services'] as $service) {
            $serviceIds[] = $service['id'];
        }
        return $serviceIds;
    }

    public function isLinked()
    {
        return ($this['relation']['navi'] || count($this['relation']['navi']));
    }

    public function getServiceLocationLinkList()
    {
        $list = array();
        $items = array(
            $this['relation']['services'],
            $this['relation']['locations'],
            $this['links']
        );
        foreach ($items as $item) {
            foreach ($item as $entity) {
                $list[\BO\Dldb\Helper\Sorter::toSortableString($entity->getName()) . '-' . $entity->getId()] = $entity;
            }
        }
        ksort($list);
        return $list;
    }

    public function getParentIdByPath($path)
    {
        if (count($this['relation']['parents']) > 1) {
            foreach ($this['relation']['parents'] as $item) {
                if ($item['path'] == $path) {
                    return $item['id'];
                }
            }
        } else 
            if (count($this['relation']['parents']) == 1) {
                return $this['relation']['parents'][0]['id'];
            }
        
        return $this['id'];
    }
}
