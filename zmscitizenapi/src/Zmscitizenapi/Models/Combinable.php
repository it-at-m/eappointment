<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class Combinable extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/combinable.json';

    /** @var array<string, array<int>> */
    private array $combinations = [];

    /** @var array<string> */
    private array $order = [];

    /**
     * Constructor.
     *
     * @param array $combinations An associative array of combinations (serviceId => providerIds).
     */
    public function __construct(array $combinations = [])
    {
        $this->order = array_keys($combinations);
        foreach ($combinations as $id => $providerIds) {
            $this->combinations[(string)$id] = is_array($providerIds) ? array_map('intval', $providerIds) : [];
        }

        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    /**
     * Get the combinations array in original format (for internal use).
     *
     * @return array<string, array<int>> The combinations as an associative array.
     */
    public function getCombinations(): array
    {
        return $this->combinations;
    }

    /**
     * Converts the model data back into an array for serialization.
     * Returns the data in numbered format:
     * {
     *   "1": { "serviceId": [providers] },
     *   "2": { "serviceId": [providers] },
     *   ...
     * }
     *
     * @return array The combinations with order preserved using numbered keys.
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->order as $index => $id) {
            $orderKey = (string)($index + 1);
            $result[$orderKey] = [$id => $this->combinations[$id]];
        }
        return $result;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
