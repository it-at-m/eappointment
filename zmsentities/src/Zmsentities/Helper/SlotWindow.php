<?php

declare(strict_types=1);

namespace BO\Zmsentities\Helper;

/**
 * Fits a new slot length into an existing opening.
 * The new window is a whole number of slots and is never longer than the old one.
 */
final class SlotWindow
{
    public static function isUnused(string $startTime, string $endTime): bool
    {
        return self::minutes($startTime) === 0 && self::minutes($endTime) === 0;
    }

    /**
     * End time of the shortened window, or null when the new slot does not fit.
     */
    public static function fittedEndTime(string $startTime, string $endTime, int $newSlotMinutes): ?string
    {
        if ($newSlotMinutes < 1 || self::isUnused($startTime, $endTime)) {
            return null;
        }

        $duration = self::minutes($endTime) - self::minutes($startTime);
        if ($duration < $newSlotMinutes) {
            return null;
        }

        $fitted = intdiv($duration, $newSlotMinutes) * $newSlotMinutes;

        return self::format(self::minutes($startTime) + $fitted);
    }

    public static function minutes(string $time): int
    {
        $time = trim($time);
        if ($time === '' || $time === '0') {
            return 0;
        }

        $parts = explode(':', $time);

        return ((int) ($parts[0] ?? 0) * 60) + (int) ($parts[1] ?? 0);
    }

    private static function format(int $minutesFromMidnight): string
    {
        return sprintf('%02d:%02d:00', intdiv($minutesFromMidnight, 60), $minutesFromMidnight % 60);
    }
}
