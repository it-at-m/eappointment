<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Model;

use BO\Zmscitizenbackend\Schema\Entity;
use JsonSerializable;

class AvailableCalendar extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/availableCalendar.json';
    public string $startDate = '';
    public string $endDate = '';
    public string $slotsStartDate = '';
    public string $slotsEndDate = '';
    public ?string $prevBookableDate = null;
    public ?string $nextBookableDate = null;
    public array $availableDays = [];

    public function __construct(
        string $startDate,
        string $endDate,
        array $availableDays = [],
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null,
        ?string $prevBookableDate = null,
        ?string $nextBookableDate = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->slotsStartDate = $slotsStartDate ?? $startDate;
        $this->slotsEndDate = $slotsEndDate ?? $endDate;
        $this->prevBookableDate = $prevBookableDate;
        $this->nextBookableDate = $nextBookableDate;
        $this->availableDays = $availableDays;
        $this->ensureValid();
    }

    public function ensureValid(): void
    {
        $this->testValid();
    }

    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'slotsStartDate' => $this->slotsStartDate,
            'slotsEndDate' => $this->slotsEndDate,
            'prevBookableDate' => $this->prevBookableDate,
            'nextBookableDate' => $this->nextBookableDate,
            'availableDays' => $this->availableDays,
        ];
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
