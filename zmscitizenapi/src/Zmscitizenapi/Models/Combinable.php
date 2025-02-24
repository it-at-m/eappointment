<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class Combinable extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/combinable.json';
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
            $this->combinations[(int)$id] = array_map('intval', $providerIds);
            $this->ensureValid();
        }
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
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
