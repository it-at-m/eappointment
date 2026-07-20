<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class AvailableDays extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/availableDays.json';
    public array $availableDays = [];

    public function __construct(array $availableDays = [])
    {
        $this->availableDays = $availableDays;
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
            'availableDays' => $this->availableDays,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
