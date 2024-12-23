<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class ThinnedProvider extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/thinnedProvider.json";

    /** @var int|null */
    public ?int $id;

    /** @var string|null */
    public ?string $name;

    /** @var string|null */
    public ?string $source;

    /** @var array|null */
    public ?array $contact;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $source = null,
        ?array $contact = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->source = $source;
        $this->contact = $contact;

        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new \InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    /**
     * Convert the ThinnedProvider object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'source' => $this->source ?? null,
            'contact' => $this->contact ?? null,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
