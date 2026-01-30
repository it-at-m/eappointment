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
    public int $id;
    public string $name;
    public ?array $address = null;
    public ?array $displayNameAlternatives = null;
    public ?bool $showAlternativeLocations = null;
    public ?string $organization = null;
    public ?string $organizationUnit = null;
    public ?int $slotTimeInMinutes = null;
    public ?array $geo = null;
    public ?array $disabledByServices = [];
    public int $priority = 1;
    public ?ThinnedScope $scope = null;
    public ?string $slotsPerAppointment = null;
    public ?int $parentId = null;

    public function __construct(
        int $id,
        string $name,
        ?array $address = null,
        ?bool $showAlternativeLocations = null,
        ?array $displayNameAlternatives = null,
        ?string $organization = null,
        ?string $organizationUnit = null,
        ?int $slotTimeInMinutes = null,
        ?array $geo = null,
        ?array $disabledByServices = [],
        int $priority = 1,
        ?ThinnedScope $scope = null,
        ?string $slotsPerAppointment = null,
        ?int $parentId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->showAlternativeLocations = $showAlternativeLocations;
        $this->displayNameAlternatives = $displayNameAlternatives;
        $this->organization = $organization;
        $this->organizationUnit = $organizationUnit;
        $this->slotTimeInMinutes = $slotTimeInMinutes;
        $this->geo = $geo;
        $this->scope = $scope;
        $this->priority = $priority;
        $this->disabledByServices = $disabledByServices;
        $this->slotsPerAppointment = $slotsPerAppointment;
        $this->parentId = $parentId;
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
            'showAlternativeLocations' => $this->showAlternativeLocations,
            'displayNameAlternatives' => $this->displayNameAlternatives,
            'organization' => $this->organization,
            'organizationUnit' => $this->organizationUnit,
            'slotTimeInMinutes' => $this->slotTimeInMinutes,
            'geo' => $this->geo,
            'disabledByServices' => $this->disabledByServices,
            'priority' => $this->priority,
            'scope' => $this->scope?->toArray(),
            'slotsPerAppointment' => $this->slotsPerAppointment,
            'parentId' => $this->parentId
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
