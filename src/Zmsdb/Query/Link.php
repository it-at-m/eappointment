<?php

namespace BO\Zmsdb\Query;

class Link extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kundenlinks';

    public function getEntityMapping()
    {
        return [
            'id' => 'link.linkid',
            'name' => 'link.beschreibung',
            'link' => 'link.link',
            'target' => 'link.neuerFrame'
        ];
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('link.behoerdenid', '=', $departmentId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Cluster $entity)
    {
        $data = array();
        $data['name'] = $entity->name;
        $data['link'] = $entity->link;
        $data['neuerFrame'] = ($entity->target)  ? 1 : 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }
}
