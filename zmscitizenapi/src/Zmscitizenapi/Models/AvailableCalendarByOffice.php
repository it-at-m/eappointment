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
    public string $slotsStartDate = '';
    public string $slotsEndDate = '';
    public array $availableDays = [];

    public function __construct(
        string $startDate,
        string $endDate,
        array $availableDays = [],
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->slotsStartDate = $slotsStartDate ?? $startDate;
        $this->slotsEndDate = $slotsEndDate ?? $endDate;
        $this->availableDays = $availableDays;
        // Skip JSON-schema validation: payload is assembled from our own zmsbackend
        // response, and Opis walks every appointment timestamp (~100ms+).
        // $this->ensureValid();
    }

    /**
     * Optional schema check for tests / non-hot paths.
     */
    public function ensureValid(): void
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
            'slotsStartDate' => $this->slotsStartDate,
            'slotsEndDate' => $this->slotsEndDate,
            'availableDays' => $this->availableDays,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
