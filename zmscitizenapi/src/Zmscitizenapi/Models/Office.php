<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use BO\Zmscitizenapi\Models\ThinnedScope;
use InvalidArgumentException;
use JsonSerializable;

class Office extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/office.json';

    /** @var int */
    public int $id;

    /** @var string */
    public string $name;

    /** @var bool|null */
    public ?bool $showAlternativeLocations;

    /** @var array|null */
    public ?array $address = null;

    /** @var array|null */
    public ?array $geo = null;

    /** @var ThinnedScope|null */
    public ?ThinnedScope $scope = null;

    /**
     * Constructor.
     *
     * @param int $id
     * @param string $name
     * @param bool $showAlternativeLocations
     * @param array|null $address
     * @param array|null $geo
     * @param ThinnedScope|null $scope
     */
    public function __construct(int $id, string $name, bool $showAlternativeLocations, ?array $address = null, ?array $geo = null, ?ThinnedScope $scope = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->showAlternativeLocations = $showAlternativeLocations;
        $this->address = $address;
        $this->geo = $geo;
        $this->scope = $scope;

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
            'id' => $this->id,
            'name' => $this->name,
            'showAlternativeLocations' => $this->showAlternativeLocations,
            'address' => $this->address,
            'geo' => $this->geo,
            'scope' => $this->scope?->toArray(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
