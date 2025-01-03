<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceRelation extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/officeServiceRelation.json';

    /** @var int */
    public int $officeId;

    /** @var int */
    public int $serviceId;

    /** @var int */
    public int $slots;

    /**
     * Constructor.
     *
     * @param int $officeId
     * @param int $serviceId
     * @param int $slots
     */
    public function __construct(int $officeId, int $serviceId, int $slots)
    {
        $this->officeId = $officeId;
        $this->serviceId = $serviceId;
        $this->slots = $slots;

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
