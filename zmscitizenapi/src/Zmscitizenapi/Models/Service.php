<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class Service extends Entity
{

    public static $schema = 'zmscitizenapi/schema/citizenapi/service.json';

    /** @var int */
    public int $id;

    /** @var string */
    public string $name;

    /**
     * Example property for maximum quantity, if relevant.
     * Adjust or remove as needed.
     *
     * @var int|null
     */
    public ?int $maxQuantity = null;

    /**
     * Constructor.
     *
     * @param int $id
     * @param string $name
     * @param int|null $maxQuantity
     */
    public function __construct(int $id, string $name, ?int $maxQuantity = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'maxQuantity' => $this->maxQuantity,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}