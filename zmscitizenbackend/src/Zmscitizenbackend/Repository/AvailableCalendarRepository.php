<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Models\AvailableCalendar;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;

class AvailableCalendarRepository
{
    private static ?self $instance = null;

    public static function use(?self $repository): void
    {
        self::$instance = $repository;
    }

    public static function create(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @param list<string|int> $officeIds
     * @param list<string|int> $serviceIds
     * @param list<string|int> $serviceCounts
     */
    public function readAvailableCalendar(
        array $officeIds,
        array $serviceIds,
        array $serviceCounts,
        string $startDate,
        string $endDate,
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null
    ): AvailableCalendar {
        try {
            $now = \App::$now instanceof \DateTimeInterface
                ? \App::$now
                : new \DateTimeImmutable('now');

            $result = (new AvailableCalendarEngine(Select::getReadConnection()))->readFromQuery(
                $now,
                $startDate,
                $endDate,
                implode(',', array_map('strval', $officeIds)),
                implode(',', array_map('strval', $serviceIds)),
                implode(',', array_map('strval', $serviceCounts)),
                $slotsStartDate,
                $slotsEndDate
            );

            return (new AvailableCalendarHydrator())->hydrate($result, $startDate, $endDate);
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }
}
