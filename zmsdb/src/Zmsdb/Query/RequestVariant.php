<?php

namespace BO\Zmsdb\Query;

class RequestVariant extends Base implements MappingInterface
{
    const TABLE = 'request_variant';
    const ALIAS = 'request_variant';

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