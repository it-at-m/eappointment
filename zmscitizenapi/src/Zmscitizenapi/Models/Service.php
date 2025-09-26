<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmscitizenapi\Models\Combinable;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class Service extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/service.json';
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
    public ?int $parentId = null;
    public ?int $variantId = null;
/**
     * Constructor.
     *
     * @param int $id
     * @param string $name
     * @param int|null $maxQuantity
     * @param int|null $parentId
     * @param int|null $variantId
 */
    public function __construct(int $id, string $name, ?int $maxQuantity = null, ?Combinable $combinable = null, ?int $parentId = null, ?int $variantId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->maxQuantity = $maxQuantity;
        $this->combinable = $combinable;
        $this->parentId = $parentId;
        $this->variantId = $variantId;
        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
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
            'combinable' => $this->combinable,
            'parent_id'   => $this->parentId,
            'variant_id'  => $this->variantId
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
