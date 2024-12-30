<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;

class ThinnedContact extends Entity implements \JsonSerializable
{
    /** @var string Points to the JSON schema file for validation */
    public static $schema = 'citizenapi/thinnedContact.json';

    public ?string $city;
    public ?string $country;
    public ?string $name;
    public ?string $postalCode;
    public ?string $region;
    public ?string $street;
    public ?string $streetNumber;

    public function __construct(
        ?string $city = null,
        ?string $country = null,
        ?string $name = null,
        ?string $postalCode = null,
        ?string $region = null,
        ?string $street = null,
        ?string $streetNumber = null
    ) {
        $this->city         = $city     ?? '';
        $this->country      = $country  ?? '';
        $this->name         = $name     ?? '';
        $this->postalCode   = $postalCode ?? '';
        $this->region       = $region   ?? '';
        $this->street       = $street   ?? '';
        $this->streetNumber = $streetNumber ?? '';

        $this->ensureValid();
    }

    /**
     * Validates the model against the JSON schema.
     *
     * @throws InvalidArgumentException if validation fails.
     */
    private function ensureValid(): void
    {
        // testValid() is inherited from Entity; it checks $this against self::$schema.
        $this->testValid();
    }

    public function toArray(): array
    {
        return [
            'city'         => $this->city,
            'country'      => $this->country,
            'name'         => $this->name,
            'postalCode'   => $this->postalCode,
            'region'       => $this->region,
            'street'       => $this->street,
            'streetNumber' => $this->streetNumber,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}