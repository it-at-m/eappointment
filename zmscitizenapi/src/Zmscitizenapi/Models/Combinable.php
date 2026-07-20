<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class Combinable extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/combinable.json';
    private array $combinations = [];
    private array $order = [];

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

    public function getCombinations(): array
    {
        return $this->combinations;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->order as $index => $id) {
            $orderKey = (string)($index + 1);
            $result[$orderKey] = [$id => $this->combinations[$id]];
        }
        return $result;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
