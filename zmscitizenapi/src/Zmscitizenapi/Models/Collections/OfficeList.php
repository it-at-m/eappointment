<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Office;
use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class OfficeList extends Entity implements JsonSerializable
{
    public static $schema = "zmsentities/schema/citizenapi/collections/officeList.json";

    /** @var Office[] */
    protected array $offices = [];

    public function __construct(array $offices = [])
    {
        foreach ($offices as $office) {
            if (!$office instanceof Office) {
                throw new \InvalidArgumentException("All elements must be instances of Office.");
            }
        }
        $this->offices = $offices;
    }

    public function toArray(): array
    {
        return [
            'offices' => array_map(fn(Office $office) => $office->toArray(), $this->offices),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
