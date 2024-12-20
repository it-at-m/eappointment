<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class Combinable extends Entity
{
    public static $schema = 'zmscitizenapi/schema/citizenapi/combinable.json';

    /** @var array */
    private array $combinations = [];

    /**
     * Constructor.
     *
     * @param array $combinations An associative array of combinations (serviceId => providerIds).
     */
    public function __construct(array $combinations = [])
    {
        foreach ($combinations as $id => $providerIds) {
            $this->combinations[(int)$id] = array_map('intval', $providerIds);
        }
    }

    /**
     * Get the combinations array.
     *
     * @return array
     */
    public function getCombinations(): array
    {
        return $this->combinations;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
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
