<?php

namespace BO\Zmsbackend\RequestVariant\Repository;

class RequestVariant extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    const TABLE = 'request_variant';
    const ALIAS = 'request_variant';

    #[\Override]
    public function getEntityMapping()
    {
        return [
            'id'   => self::TABLE . '.id',
            'name' => self::TABLE . '.name',
        ];
    }

    public function orderByName()
    {
        $this->query->orderBy(self::TABLE . '.name', 'ASC');
        return $this;
    }
}
