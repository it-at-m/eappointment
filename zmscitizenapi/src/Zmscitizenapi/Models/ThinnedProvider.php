<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ThinnedProvider extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/thinnedProvider.json";
    public ?int $id;
    public ?string $name;
    public ?string $displayName;
    public ?string $source;
    public ?float $lat;
    public ?float $lon;
    public ?ThinnedContact $contact;
    public function __construct(?int $id = null, ?string $name = null, ?string $displayName = null, ?float $lat = null, ?float $lon = null, ?string $source = null, ?ThinnedContact $contact = null,)
    {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->lat = $lat;
        $this->lon = $lon;
        $this->source = $source;
        $this->contact = $contact;
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
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'displayName' => $this->displayName ?? null,
            'lat' => $this->lat ?? null,
            'lon' => $this->lon ?? null,
            'source' => $this->source ?? null,
            'contact' => $this->contact ?? null,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
