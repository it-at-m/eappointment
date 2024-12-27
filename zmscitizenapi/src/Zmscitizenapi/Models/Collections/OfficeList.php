<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Office;
use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class OfficeList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/officeList.json";

    /** @var Office[] */
    protected array $offices = [];

    public function __construct(array $offices = [])
    {
        foreach ($offices as $office) {
            try {
                if (!$office instanceof Office) {
                    throw new \InvalidArgumentException("Element is not an instance of Office.");
                }
                $this->offices[] = $office;
            } catch (\Exception $e) {
                error_log("Invalid Office encountered: " . $e->getMessage()); //Gracefully handle
            }
        }

        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new \InvalidArgumentException("The provided data is invalid according to the schema.");
        }
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
