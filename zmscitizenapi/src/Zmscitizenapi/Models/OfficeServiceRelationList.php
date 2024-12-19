<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class OfficeServiceRelationList extends Entity implements JsonSerializable
{
    public static $schema = "zmsentities/schema/citizenapi/officeServiceRelationList.json";

    /** @var OfficeServiceRelation[] */
    protected array $relations = [];

    public function __construct(array $relations = [])
    {
        foreach ($relations as $relation) {
            if (!$relation instanceof OfficeServiceRelation) {
                throw new \InvalidArgumentException("All elements must be instances of OfficeServiceRelation.");
            }
        }
        $this->relations = $relations;
    }

    public function toArray(): array
    {
        return [
            'relations' => array_map(fn(OfficeServiceRelation $relation) => $relation->toArray(), $this->relations),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
