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

    /** @var array|null */
    public ?array $address = null;

    /** @var array|null */
    public ?array $displayNameAlternatives = null;

    /** @var string|null */
    public ?string $organization = null;

    /** @var string|null */
    public ?string $organizationUnit = null;

    /** @var int|null */
    public ?int $slotTimeInMinutes = null;

    /** @var array|null */
    public ?array $geo = null;

    /** @var ThinnedScope|null */
    public ?ThinnedScope $scope = null;

    public function __construct(
        int $id,
        string $name,
        ?array $address = null,
        ?array $displayNameAlternatives = null,
        ?string $organization = null,
        ?string $organizationUnit = null,
        ?int $slotTimeInMinutes = null,
        ?array $geo = null,
        ?ThinnedScope $scope = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->displayNameAlternatives = $displayNameAlternatives;
        $this->organization = $organization;
        $this->organizationUnit = $organizationUnit;
        $this->slotTimeInMinutes = $slotTimeInMinutes;
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
            'address' => $this->address,
            'displayNameAlternatives' => $this->displayNameAlternatives,
            'organization' => $this->organization,
            'organizationUnit' => $this->organizationUnit,
            'slotTimeInMinutes' => $this->slotTimeInMinutes,
            'geo' => $this->geo,
            'scope' => $this->scope?->toArray(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
