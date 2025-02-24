<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
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
/** @var float|null */
    public ?float $lat;
/** @var float|null */
    public ?float $lon;
/** @var ThinnedContact|null */
    public ?ThinnedContact $contact;
    public function __construct(?int $id = null, ?string $name = null, ?float $lat = null, ?float $lon = null, ?string $source = null, ?ThinnedContact $contact = null,)
    {
        $this->id = $id;
        $this->name = $name;
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
            'lat' => $this->lat ?? null,
            'lon' => $this->lon ?? null,
            'source' => $this->source ?? null,
            'contact' => $this->contact ?? null,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
