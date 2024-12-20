<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class AvailableAppointments extends Entity
{
    public static $schema = 'zmscitizenapi/schema/citizenapi/availableAppointments.json';

    /** @var array */
    public array $appointmentTimestamps = [];

    public function __construct(array $appointmentTimestamps = [])
    {
        $this->appointmentTimestamps = array_map('intval', $appointmentTimestamps);
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

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}