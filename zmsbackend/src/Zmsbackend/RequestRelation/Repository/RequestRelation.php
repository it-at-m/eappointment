<?php

namespace BO\Zmsbackend\RequestRelation\Repository;

class RequestRelation extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    const string TABLE = 'request_provider';

    const string ALIAS = 'request_provider';

    /**
     * @return string[]
     *
     */
    #[\Override]
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
     * @return \BO\Zmsbackend\Query\Builder\Expression[]
     *
     */
    #[\Override]
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
