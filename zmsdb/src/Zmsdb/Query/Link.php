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
     *
     * @var int
     */
    protected int $resolveLevel = 0;

    /**
     * @return string[]
     *
     * @psalm-return array{id: 'link.linkid', name: 'link.beschreibung', url: 'link.link', target: 'link.neuerFrame', public: 'link.oeffentlich', organisation: 'link.organisationsid'}
     */
    public function getEntityMapping(): array
    {
        return [
            'id' => 'link.linkid',
            'name' => 'link.beschreibung',
            'url' => 'link.link',
            'target' => 'link.neuerFrame',
            'public' => 'link.oeffentlich',
            'organisation' => 'link.organisationsid'
        ];
    }

    public function addConditionLinkId($linkId): static
    {
        $this->query->where('link.linkid', '=', $linkId);
        return $this;
    }

    public function addConditionDepartmentId($departmentId): static
    {
        $this->leftJoin(
            new Alias('behoerde', 'link_department'),
            'link_department.OrganisationsID',
            '=',
            'link.organisationsid'
        );
        $this->query->where('link_department.BehoerdenID', '=', $departmentId);
        $this->query->orWhere('link.behoerdenid', '=', $departmentId);
        return $this;
    }

    /**
     * @return (int|mixed)[]
     *
     * @psalm-return array<string, 0|1|mixed>
     */
    public function reverseEntityMapping(\BO\Zmsentities\Link $entity, $departmentId): array
    {
        $data = array();
        $data['behoerdenid'] = ($entity->organisation) ? 0 : $departmentId;
        $data['organisationsid'] = $entity->organisation;
        $data['beschreibung'] = $entity->name;
        $data['link'] = $entity->url;
        $data['neuerFrame'] = ($entity->target)  ? 1 : 0;
        $data['oeffentlich'] = ($entity->public)  ? 1 : 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }
}
