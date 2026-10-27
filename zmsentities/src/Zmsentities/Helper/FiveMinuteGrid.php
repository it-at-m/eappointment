<?php

declare(strict_types=1);

namespace BO\Zmsentities\Helper;

/**
 * Every location books on a fixed 5-minute grid.
 * A service duration is a count of those slots, so changing 60 minutes to 90
 * does not move the opening-hours grid.
 */
class FiveMinuteGrid
{
    public const int MINUTES = 5;

    public static function isMultiple(int $minutes): bool
    {
        return $minutes > 0 && $minutes % self::MINUTES === 0;
    }

    public static function slotsForDuration(int $durationMinutes): int
    {
        if ($durationMinutes < 1) {
            return 0;
        }

        return intdiv($durationMinutes, self::MINUTES);
    }

    /**
     * The step between start times offered in the calendar.
     * This is the greatest common divisor of the service durations, which is
     * the step the calendar used before every location was stored as 5 minutes.
     *
     * @param list<int> $durationMinutes
     */
    public static function appointmentInterval(array $durationMinutes): int
    {
        $interval = 0;
        foreach ($durationMinutes as $duration) {
            $duration = (int) $duration;
            if ($duration < 1) {
                continue;
            }
            $interval = $interval === 0 ? $duration : self::greatestCommonDivisor($interval, $duration);
        }
        if ($interval >= self::MINUTES && $interval % self::MINUTES === 0) {
            return $interval;
        }

        return self::MINUTES;
    }

    private static function greatestCommonDivisor(int $a, int $b): int
    {
        while ($b !== 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }

        return abs($a);
    }

    /**
     * Keep the same maximum appointment length in minutes.
     * An empty limit stays empty. Returns null when nothing should be written.
     */
    public static function scaledSlotsPerAppointment(?int $current, int $oldSlotMinutes, int $newSlotMinutes = self::MINUTES): ?int
    {
        if ($current === null || $current < 1 || $oldSlotMinutes < 1 || $newSlotMinutes < 1) {
            return null;
        }
        if ($oldSlotMinutes === $newSlotMinutes) {
            return null;
        }

        return intdiv(($current * $oldSlotMinutes) + $newSlotMinutes - 1, $newSlotMinutes);
    }
}
