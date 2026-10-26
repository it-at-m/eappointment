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
}
