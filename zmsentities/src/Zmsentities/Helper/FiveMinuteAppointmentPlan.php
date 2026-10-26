<?php

declare(strict_types=1);

namespace BO\Zmsentities\Helper;

/**
 * Which clock times an existing appointment needs once the grid is 5 minutes.
 * The parent row keeps the start time. Every later time is a Folgetermin.
 */
class FiveMinuteAppointmentPlan
{
    /**
     * @param list<string> $existingTimes times already stored, including the parent
     * @return list<string>|null follow-up times still missing, or null when the length cannot stay exact
     */
    public static function missingFollowUpTimes(
        string $start,
        array $existingTimes,
        int $slotCount,
        int $slotMinutes
    ): ?array {
        $grid = self::gridTimes($start, $slotCount, $slotMinutes);
        if ($grid === null) {
            return null;
        }

        $have = [];
        foreach ($existingTimes as $time) {
            $normalized = self::normalizeTime($time);
            if ($normalized !== null) {
                $have[$normalized] = true;
            }
        }

        $missing = [];
        foreach ($grid as $index => $time) {
            if ($index === 0 || isset($have[$time])) {
                continue;
            }
            $missing[] = $time;
        }

        return $missing;
    }

    public static function followUpCount(int $slotCount, int $slotMinutes): ?int
    {
        $grid = self::gridTimes('00:00:00', $slotCount, $slotMinutes);
        if ($grid === null) {
            return null;
        }

        return count($grid) - 1;
    }

    /**
     * @return list<string>|null clock times from the start, every 5 minutes, for the same length
     */
    public static function gridTimes(string $start, int $slotCount, int $slotMinutes): ?array
    {
        if ($slotCount < 1 || !FiveMinuteGrid::isMultiple($slotMinutes)) {
            return null;
        }

        $startTime = self::normalizeTime($start);
        if ($startTime === null) {
            return null;
        }

        $origin = \DateTimeImmutable::createFromFormat('!H:i:s', $startTime);
        if ($origin === false) {
            return null;
        }

        $steps = intdiv($slotCount * $slotMinutes, FiveMinuteGrid::MINUTES);
        if ($steps < 1) {
            return null;
        }

        $times = [];
        for ($step = 0; $step < $steps; $step++) {
            $formatted = $origin->modify('+' . ($step * FiveMinuteGrid::MINUTES) . ' minutes')->format('H:i:s');
            if ($step > 0 && $formatted <= $times[0]) {
                return null;
            }
            $times[] = $formatted;
        }

        return $times;
    }

    public static function normalizeTime(string $time): ?string
    {
        if (preg_match('/^(\d{2}:\d{2}:\d{2})/', $time, $matches) === 1) {
            return $matches[1];
        }
        if (preg_match('/^(\d{2}:\d{2})$/', $time, $matches) === 1) {
            return $matches[1] . ':00';
        }

        return null;
    }
}
