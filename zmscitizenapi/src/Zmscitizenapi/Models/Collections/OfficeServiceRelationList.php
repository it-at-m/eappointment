<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\OfficeServiceRelation;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceRelationList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/officeServiceRelationList.json";

    /** @var OfficeServiceRelation[] */
    protected array $relations = [];

    public function __construct(array $relations = [])
    {

        foreach ($relations as $relation) {
            try {
                if (!$relation instanceof OfficeServiceRelation) {
                    throw new InvalidArgumentException("Element is not an instance of OfficeServiceRelation.");
                }
                $this->relations[] = $relation;
            } catch (\Exception $e) {
                error_log("Invalid OfficeServiceRelation encountered: " . $e->getMessage()); //Gracefully handle
            }
        }

        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
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
