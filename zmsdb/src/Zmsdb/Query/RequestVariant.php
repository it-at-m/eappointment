<?php

namespace BO\Zmsdb\Query;

class RequestVariant extends Base implements MappingInterface
{
    const TABLE = 'request_variant';
    const ALIAS = 'request_variant';

    /**
     * @return string[]
     *
     * @psalm-return array{id: 'request_variant.id', name: 'request_variant.name'}
     */
    public function getEntityMapping()
    {
        return [
            'id'   => self::TABLE . '.id',
            'name' => self::TABLE . '.name',
        ];
    }

    public function orderByName(): static
    {
        $this->query->orderBy(self::TABLE . '.name', 'ASC');
        return $this;
    }
}
