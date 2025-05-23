<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceRelation extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/officeServiceRelation.json';

    public int $officeId;
    public int $serviceId;
    public int $slots;
    public bool $public;
    public ?int $maxQuantity;

    /**
     * Constructor.
     *
     * @param int $officeId
     * @param int $serviceId
     * @param int $slots
     * @param bool $public
     * @param ?int $maxQuantity
     */
    public function __construct(int $officeId, int $serviceId, int $slots, bool $public, ?int $maxQuantity)
    {
        $this->officeId = $officeId;
        $this->serviceId = $serviceId;
        $this->slots = $slots;
        $this->public = $public;
        $this->maxQuantity = $maxQuantity;
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
            'officeId' => $this->officeId,
            'serviceId' => $this->serviceId,
            'slots' => $this->slots,
            'public' => $this->public,
            'maxQuantity' => $this->maxQuantity,
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
