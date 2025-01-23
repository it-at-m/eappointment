<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ProcessFreeSlotsGroupByOffice extends Entity implements JsonSerializable
{

    public static $schema = 'citizenapi/processFreeSlots.json';

    /** @var array|null */
    public array|null $appointmentTimestamps = [];

    /**
     * @param array $appointmentTimestamps
     */
    public function __construct(array $appointmentTimestamps = [])
    {

        $this->appointmentTimestamps = array_map('intval', $appointmentTimestamps);

        $this->ensureValid();
    }

    private function ensureValid(): void
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException('The provided data is invalid according to the schema.');
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
            'appointmentTimestamps' => $this->appointmentTimestamps,
        ];
    }

    /**
     * Implementation of JsonSerializable.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}