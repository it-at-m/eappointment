<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class OfficeServiceRelation extends Entity
{
    public static $schema = 'zmscitizenapi/schema/citizenapi/officeServiceRelation.json';

    /** @var string */
    public string $officeId;

    /** @var string */
    public string $serviceId;

    /** @var int */
    public int $slots;

    /**
     * Constructor.
     *
     * @param string $officeId
     * @param string $serviceId
     * @param int $slots
     */
    public function __construct(string $officeId, string $serviceId, int $slots)
    {
        $this->officeId = $officeId;
        $this->serviceId = $serviceId;
        $this->slots = $slots;
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'officeId' => $this->officeId,
            'serviceId' => $this->serviceId,
            'slots' => $this->slots,
        ];
    }

    /**
     * Implements JSON serialization.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
