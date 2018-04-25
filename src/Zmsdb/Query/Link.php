<?php

namespace BO\Zmsdb\Query;

class Link extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kundenlinks';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getEntityMapping()
    {
        return [
            'id' => 'link.linkid',
            'name' => 'link.beschreibung',
            'url' => 'link.link',
            'target' => 'link.neuerFrame'
        ];
    }

    public function addConditionLinkId($linkId)
    {
        $this->query->where('link.linkid', '=', $linkId);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('link.behoerdenid', '=', $departmentId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->leftJoin(
            new Alias('standort', 'link_scope'),
            'link_scope.BehoerdenID',
            '=',
            'link.behoerdenid'
        );
        $this->query->where('link_scope.StandortID', '=', $scopeId);
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
