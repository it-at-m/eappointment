<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class AvailableAppointments extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/availableAppointments.json';
    public array $appointmentTimestamps = [];

    public function __construct(array $appointmentTimestamps = [])
    {
        $this->appointmentTimestamps = array_map('intval', $appointmentTimestamps);
        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    public function toArray(): array
    {
        return [
            'appointmentTimestamps' => $this->appointmentTimestamps,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
