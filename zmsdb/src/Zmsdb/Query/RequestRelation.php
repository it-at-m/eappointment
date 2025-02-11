<?php

namespace BO\Zmsdb\Query;

class RequestRelation extends Base implements MappingInterface
{
    const TABLE = 'request_provider';

    const ALIAS = 'request_provider';

    public function getEntityMapping()
    {
        return [
            'request__id' => self::TABLE . '.request__id',
            'provider__id' => self::TABLE . '.provider__id',
            'source' => self::TABLE . '.source',
            'slots' => self::TABLE . '.slots'
        ];
    }

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

    public function addConditionRequestId($requestId)
    {
        $this->query->where(self::TABLE . '.request__id', '=', $requestId);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where(self::TABLE . '.provider__id', '=', $providerId);
        return $this;
    }

    public function addConditionBookable()
    {
        $this->query->where(self::TABLE . '.bookable', '=', 1);
        return $this;
    }

    public function addConditionSource($sourceName)
    {
        $this->query->where(self::TABLE . '.source', '=', $sourceName);
        return $this;
    }
}
