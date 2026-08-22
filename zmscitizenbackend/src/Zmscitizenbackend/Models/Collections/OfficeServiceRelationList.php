<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Models\Collections;

use BO\Zmscitizenbackend\Models\OfficeServiceRelation;
use BO\Zmscitizenbackend\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceRelationList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/officeServiceRelationList.json";
    public array $relations = [];
    public function __construct(array $relations = [])
    {

        foreach ($relations as $relation) {
            try {
                if (!$relation instanceof OfficeServiceRelation) {
                    throw new InvalidArgumentException("Element is not an instance of OfficeServiceRelation.");
                }
                $this->relations[] = $relation;
            } catch (\Exception $e) {
                \App::$log->warning('Invalid OfficeServiceRelation skipped', ['exception' => $e->getMessage()]);
            }
        }

        $this->ensureValid();
    }

    /**
     * @return void
     */
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

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
