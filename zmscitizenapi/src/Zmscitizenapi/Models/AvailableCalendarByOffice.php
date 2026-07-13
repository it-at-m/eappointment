<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class AvailableCalendarByOffice extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/availableCalendarByOffice.json';
    public string $startDate = '';
    public string $endDate = '';
    public array $availableDays = [];

    public function __construct(string $startDate, string $endDate, array $availableDays = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->availableDays = $availableDays;
        $this->ensureValid();
    }

    private function ensureValid(): void
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException('The provided data is invalid according to the schema.');
        }
    }

    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'availableDays' => $this->availableDays,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
