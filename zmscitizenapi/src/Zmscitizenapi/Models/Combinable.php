<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class Combinable extends Entity implements JsonSerializable
{
    public static $schema = 'zmsentities/schema/citizenapi/combinable.json';

    /** @var array<int, int[]> */
    private array $combinations = [];

    /**
     * Constructor.
     *
     * @param array $combinations An associative array of combinations (serviceId => providerIds).
     */
    public function __construct(array $combinations = [])
    {
        foreach ($combinations as $id => $providerIds) {
            // Ensure both keys (service IDs) and values (provider IDs) are integers.
            $this->combinations[(int)$id] = array_map('intval', $providerIds);
        }
    }

    /**
     * Get the combinations array.
     *
     * @return array<int, int[]> The combinations as an associative array of integers.
     */
    public function getCombinations(): array
    {
        return $this->combinations;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array<int, int[]> The combinations as an associative array of integers.
     */
    public function toArray(): array
    {
        return $this->combinations;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
