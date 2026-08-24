<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Repository;

use BO\Zmscitizenbackend\Availability\Model\AvailableCalendar;

class AvailableCalendarHydrator
{
    /**
     * @param array<string, mixed> $availability
     */
    public function hydrate(array $availability, string $startDate, string $endDate): AvailableCalendar
    {
        $formattedDays = [];
        foreach ($availability['days'] ?? [] as $day) {
            $offices = [];
            foreach ($day['appointments'] ?? [] as $officeId => $timestamps) {
                $offices[] = [
                    'officeId' => (string) $officeId,
                    'appointments' => array_map('intval', array_values((array) $timestamps)),
                ];
            }

            $formattedDays[] = [
                'date' => (string) ($day['date'] ?? ''),
                'providerIDs' => (string) ($day['providerIDs'] ?? ''),
                'offices' => $offices,
            ];
        }

        $prevBookableDate = $availability['prevBookableDate'] ?? null;
        $nextBookableDate = $availability['nextBookableDate'] ?? null;

        return new AvailableCalendar(
            (string) ($availability['startDate'] ?? $startDate),
            (string) ($availability['endDate'] ?? $endDate),
            $formattedDays,
            (string) ($availability['slotsStartDate'] ?? $startDate),
            (string) ($availability['slotsEndDate'] ?? $endDate),
            $prevBookableDate !== null ? (string) $prevBookableDate : null,
            $nextBookableDate !== null ? (string) $nextBookableDate : null
        );
    }
}
