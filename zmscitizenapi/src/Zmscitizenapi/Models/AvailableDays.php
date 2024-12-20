<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class AvailableDays extends Entity implements JsonSerializable
{
    public static $schema = 'zmscitizenapi/schema/citizenapi/availableDays.json';

    /** @var array */
    public array $availableDays = [];

    public function __construct(array $availableDays = [])
    {
        $this->availableDays = $availableDays;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'availableDays' => $this->availableDays,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
