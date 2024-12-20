<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use BO\Zmscitizenapi\Models\ThinnedScope;
use JsonSerializable;

class Office extends Entity implements JsonSerializable
{
    public static $schema = 'zmsentities/schema/citizenapi/office.json';

    /** @var int */
    public int $id;

    /** @var string */
    public string $name;

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
     * @param array|null $address
     * @param array|null $geo
     * @param ThinnedScope|null $scope
     */
    public function __construct(int $id, string $name, ?array $address = null, ?array $geo = null, ?ThinnedScope $scope = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->geo = $this->validateGeo($geo);
        $this->scope = $scope;
    }

    /**
     * Validate and convert geo data to an array of floats.
     *
     * @param array|null $geo
     * @return array|null
     * @throws \InvalidArgumentException If geo values are out of bounds.
     */
    private function validateGeo(?array $geo): ?array
    {
        if (isset($geo['lat'], $geo['lon'])) {
            $lat = (float)$geo['lat'];
            $lon = (float)$geo['lon'];

            if ($lat < -90 || $lat > 90) {
                throw new \InvalidArgumentException("Latitude must be between -90 and 90.");
            }

            if ($lon < -180 || $lon > 180) {
                throw new \InvalidArgumentException("Longitude must be between -180 and 180.");
            }

            return ['lat' => $lat, 'lon' => $lon];
        }
        return null;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'address' => $this->address,
            'geo'     => $this->geo,
            'scope'   => $this->scope ? [
                'id' => $this->scope->id,
                'provider' => $this->scope->getProvider(),
                'shortName' => $this->scope->getShortName(),
                'telephoneActivated' => $this->scope->getTelephoneActivated(),
                'telephoneRequired' => $this->scope->getTelephoneRequired(),
                'customTextfieldActivated' => $this->scope->getCustomTextfieldActivated(),
                'customTextfieldRequired' => $this->scope->getCustomTextfieldRequired(),
                'customTextfieldLabel' => $this->scope->getCustomTextfieldLabel(),
                'captchaActivatedRequired' => $this->scope->getCaptchaActivatedRequired(),
                'displayInfo' => $this->scope->getDisplayInfo(),
            ] : null,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
