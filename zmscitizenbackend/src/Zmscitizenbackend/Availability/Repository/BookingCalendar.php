<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Repository;

/**
 * Internal calendar state for available-calendar and reserve.
 * Replaces the zmsentities Calendar/Day/Scope hop inside those engines.
 */
class BookingCalendar
{
    /** @var array{year: int, month: int, day: int} */
    public array $firstDay = ['year' => 0, 'month' => 0, 'day' => 0];

    /** @var array{year: int, month: int, day: int}|null */
    public ?array $lastDay = null;

    /** @var list<array{id: int, source: string}> */
    public array $providers = [];

    /** @var list<array{id: mixed, source: string}> */
    public array $requests = [];

    /** @var array<int|string, array<string, mixed>> */
    public array $scopes = [];

    /** @var array<int|string, int|float> */
    public array $slotsByScopeId = [];

    /** @var array<int|string, array<string, mixed>> */
    public array $days = [];

    public function firstDateTime(): \DateTimeImmutable
    {
        return self::dateTimeFromParts($this->firstDay)->setTime(0, 0, 0);
    }

    public function lastDateTime(bool $createIfNotProvided = true): ?\DateTimeImmutable
    {
        if (!$createIfNotProvided && $this->lastDay === null) {
            return null;
        }

        return self::dateTimeFromParts($this->lastDay ?? $this->firstDay)->setTime(23, 59, 59);
    }

    /**
     * First day of each month covered by firstDay..lastDay.
     *
     * @return list<\DateTimeImmutable>
     */
    public function monthStarts(): array
    {
        $first = $this->firstDateTime()->modify('first day of this month')->setTime(0, 0, 0);
        $last = $this->lastDateTime()->modify('last day of this month')->setTime(23, 59, 59);
        $current = $first;
        if ($first->getTimestamp() > $last->getTimestamp()) {
            $current = $last->modify('first day of this month')->setTime(0, 0, 0);
            $last = $first->modify('last day of this month')->setTime(23, 59, 59);
        }

        $months = [];
        do {
            $months[] = $current;
            $current = $current->modify('+1 month');
        } while ($current->getTimestamp() < $last->getTimestamp());

        return $months;
    }

    /**
     * @param array<string, mixed> $scope
     */
    public function requiredSlotsFor(array $scope): int|float
    {
        $scopeId = $scope['id'] ?? null;
        if ($scopeId === null || !isset($this->slotsByScopeId[$scopeId])) {
            return 0;
        }

        return $this->slotsByScopeId[$scopeId];
    }

    public function addRequiredSlots(string $source, mixed $providerId, int $slotsRequired): void
    {
        foreach ($this->scopes as $scope) {
            $provider = $scope['provider'] ?? [];
            if (($provider['source'] ?? null) != $source || ($provider['id'] ?? null) != $providerId) {
                continue;
            }
            $scopeId = $scope['id'] ?? null;
            if ($scopeId === null) {
                continue;
            }
            if (!isset($this->slotsByScopeId[$scopeId])) {
                $this->slotsByScopeId[$scopeId] = 0;
            }
            $this->slotsByScopeId[$scopeId] += $slotsRequired;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function scopeById(mixed $scopeId): ?array
    {
        foreach ($this->scopes as $scope) {
            if (($scope['id'] ?? null) == $scopeId) {
                return $scope;
            }
        }

        return null;
    }

    public function uniqueScopes(): void
    {
        $unique = [];
        foreach ($this->scopes as $scope) {
            $scopeId = $scope['id'] ?? null;
            if ($scopeId && !isset($unique[$scopeId])) {
                $unique[$scopeId] = $scope;
            }
        }
        $this->scopes = $unique;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function nestRow(array $row): array
    {
        $hash = $row;
        foreach ($row as $key => $value) {
            if (!is_string($key) || !str_contains($key, '__')) {
                continue;
            }
            unset($hash[$key]);
            $current =& $hash;
            foreach (explode('__', $key) as $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current =& $current[$part];
            }
            $current = $value;
            unset($current);
        }

        return $hash;
    }

    /**
     * @param array<string, mixed> $day
     * @return array<string, mixed>
     */
    public static function applyDayStatus(array $day, string $slotType, \DateTimeInterface $now): array
    {
        $freeCount = $day['freeAppointments'][$slotType] ?? null;
        $allCount = $day['allAppointments'][$slotType] ?? null;
        $hasAppointments = (null !== $allCount && $allCount <= 0) ? null : (0 < (int) $freeCount);

        $status = $day['status'] ?? 'notBookable';
        if ($status !== 'restricted' && $hasAppointments !== null) {
            $status = $hasAppointments ? 'bookable' : 'full';
        } elseif ($hasAppointments === null) {
            $status = 'notBookable';
        }

        if (self::dayToDateTime($day)->getTimestamp() + 86400 <= $now->getTimestamp() + 1800) {
            $status = 'restricted';
        }

        $day['status'] = $status;

        return $day;
    }

    /**
     * @param array<int|string, array<string, mixed>> $days
     * @return array<int|string, array<string, mixed>>
     */
    public static function withStatusByType(array $days, string $slotType, \DateTimeInterface $now): array
    {
        foreach ($days as $key => $day) {
            $days[$key] = self::applyDayStatus($day, $slotType, $now);
        }

        return $days;
    }

    /**
     * @param array<int|string, array<string, mixed>> $days
     * @return array<int|string, array<string, mixed>>
     */
    public static function daysInRange(
        array $days,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $rangeStart = (new \DateTimeImmutable($startDate->format('Y-m-d H:i:s'), $startDate->getTimezone()))
            ->setTime(0, 0, 0);
        $rangeEnd = (new \DateTimeImmutable($endDate->format('Y-m-d H:i:s'), $endDate->getTimezone()))
            ->setTime(23, 59, 59);
        $filtered = [];
        foreach ($days as $key => $day) {
            $dayTime = self::dayToDateTime($day);
            if ($dayTime >= $rangeStart && $dayTime <= $rangeEnd) {
                $filtered[$key] = $day;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $day
     */
    public static function dayHash(array $day): string
    {
        return str_pad((string) ($day['day'] ?? ''), 2, '0', STR_PAD_LEFT)
            . '-'
            . str_pad((string) ($day['month'] ?? ''), 2, '0', STR_PAD_LEFT)
            . '-'
            . ($day['year'] ?? '');
    }

    /**
     * @param array<string, mixed> $day
     */
    public static function dayIso(array $day): string
    {
        return sprintf(
            '%04d-%02d-%02d',
            (int) ($day['year'] ?? 0),
            (int) ($day['month'] ?? 0),
            (int) ($day['day'] ?? 0)
        );
    }

    /**
     * @param array<string, mixed> $day
     */
    public static function dayToDateTime(array $day): \DateTimeImmutable
    {
        return self::dateTimeFromParts([
            'year' => (int) ($day['year'] ?? 0),
            'month' => (int) ($day['month'] ?? 0),
            'day' => (int) ($day['day'] ?? 0),
        ]);
    }

    /**
     * @param array{year?: int, month?: int, day?: int} $parts
     */
    public static function dateTimeFromParts(array $parts): \DateTimeImmutable
    {
        $iso = sprintf(
            '%04d-%02d-%02d',
            (int) ($parts['year'] ?? 0),
            (int) ($parts['month'] ?? 0),
            (int) ($parts['day'] ?? 0)
        );
        try {
            return (new \DateTimeImmutable($iso))->setTime(0, 0, 0);
        } catch (\Exception) {
            return (new \DateTimeImmutable('@0'))->setTime(0, 0, 0);
        }
    }
}
