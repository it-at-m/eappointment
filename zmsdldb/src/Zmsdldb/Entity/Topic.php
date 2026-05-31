<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Entity;

/**
 * Helper for topics export
 */
class Topic extends Base
{
    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getServiceIds(): array
    {
        $serviceIds = array();
        foreach ($this['relation']['services'] as $service) {
            $serviceIds[] = $service['id'];
        }
        return $serviceIds;
    }

    public function isLinked(): bool
    {
        return ($this['relation']['navi'] || static::subcount($this['relation']['navi']));
    }

    public function getServiceLocationLinkList(): \BO\Zmsdldb\Collection\Base
    {
        $list = new \BO\Zmsdldb\Collection\Base();
        $items = array(
            $this['relation']['services'],
            $this['relation']['locations'],
            $this['links']
        );
        foreach ($items as $item) {
            foreach ($item as $entity) {
                $list[] = $entity;
            }
        }
        return $list;
    }

    public function getParentId()
    {
        if (count($this['relation']['parents']) > 1) {
            foreach ($this['relation']['parents'] as $item) {
                if ($item['path'] == $this['path']) {
                    return $item['id'];
                }
            }
        } elseif (count($this['relation']['parents']) == 1) {
            return $this['relation']['parents'][0]['id'];
        }
        return $this['id'];
    }
}
