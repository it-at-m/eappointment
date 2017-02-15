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
            'url' => 'link.link',
            'target' => 'link.neuerFrame'
        ];
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('link.behoerdenid', '=', $departmentId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Link $entity, $departmentId)
    {
        $data = array();
        $data['behoerdenid'] = $departmentId;
        $data['beschreibung'] = $entity->name;
        $data['link'] = $entity->url;
        $data['neuerFrame'] = ($entity->target)  ? 1 : 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }
}
