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

    /** @var Combinable */
    public ?Combinable $combinable = null;

    /**
     * Constructor.
     *
     * @param int $id
     * @param string $name
     * @param int|null $maxQuantity
     */
    public function __construct(int $id, string $name, ?int $maxQuantity = null, ?Combinable $combinable = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->maxQuantity = $maxQuantity;
        $this->combinable = $combinable;
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
            'combinable' => $this->combinable
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}