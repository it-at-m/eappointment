<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ThinnedProvider extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/thinnedProvider.json";

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
