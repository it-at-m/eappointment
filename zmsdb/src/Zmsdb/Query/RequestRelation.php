<?php

namespace BO\Zmsdb\Query;

class RequestRelation extends Base implements MappingInterface
{
    const TABLE = 'request_provider';

    const ALIAS = 'request_provider';

    /**
     * @return string[]
     *
     * @psalm-return array{request__id: 'request_provider.request__id', provider__id: 'request_provider.provider__id', source: 'request_provider.source', slots: 'request_provider.slots', public: 'request_provider.public_visibility', maxQuantity: 'request_provider.max_quantity'}
     */
    public function getEntityMapping()
    {
        return [
            'request__id' => self::TABLE . '.request__id',
            'provider__id' => self::TABLE . '.provider__id',
            'source' => self::TABLE . '.source',
            'slots' => self::TABLE . '.slots',
            'public' => self::TABLE . '.public_visibility',
            'maxQuantity' => self::TABLE . '.max_quantity'
        ];
    }

    /**
     * @return Builder\Expression[]
     *
     * @psalm-return array{'request__$ref': Builder\Expression, 'provider__$ref': Builder\Expression}
     */
    public function getReferenceMapping()
    {
        return [
            'request__$ref' => self::expression(
                'CONCAT("/request/", `' . self::TABLE . '`.`source`, "/", `' . self::TABLE . '`.`request__id`, "/")'
            ),
            'provider__$ref' => self::expression(
                'CONCAT("/provider/", `' . self::TABLE . '`.`source`, "/", `' . self::TABLE . '`.`provider__id`, "/")'
            )
        ];
    }

    public function addConditionRequestId($requestId): static
    {
        $this->query->where(self::TABLE . '.request__id', '=', $requestId);
        return $this;
    }

    public function addConditionProviderId($providerId): static
    {
        $this->query->where(self::TABLE . '.provider__id', '=', $providerId);
        return $this;
    }

    public function addConditionBookable(): static
    {
        $this->query->where(self::TABLE . '.bookable', '=', 1);
        return $this;
    }

    public function addConditionSource($sourceName): static
    {
        $this->query->where(self::TABLE . '.source', '=', $sourceName);
        return $this;
    }
}
