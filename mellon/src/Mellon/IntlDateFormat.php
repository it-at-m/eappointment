<?php

namespace BO\Mellon;

/**
 * ICU date formatting with safe fallback when ext/intl cannot resolve Olson zones
 * (e.g. apache2handler + broken ICU tzdata in some container/VM setups).
 */
final class IntlDateFormat
{
    public static function format(int $timestamp, string $pattern, string $locale = 'de_DE', $timezone = 'Europe/Berlin'): string
    {
        $timezoneId = $timezone instanceof \DateTimeZone ? $timezone->getName() : (string) $timezone;

        if (\extension_loaded('intl') && \class_exists(\IntlDateFormatter::class)) {
            $tzArg = ($timezoneId === \date_default_timezone_get()) ? null : $timezoneId;
            foreach ([$tzArg, null] as $tryTz) {
                try {
                    $formatter = new \IntlDateFormatter(
                        $locale,
                        \IntlDateFormatter::NONE,
                        \IntlDateFormatter::NONE,
                        $tryTz,
                        \IntlDateFormatter::GREGORIAN,
                        $pattern
                    );
                    $out = $formatter->format($timestamp);
                    if (false !== $out && '' !== $out) {
                        return $out;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return self::phpFallback($timestamp, $pattern, $timezoneId);
    }

    private static function phpFallback(int $timestamp, string $pattern, string $timezoneId): string
    {
        $dt = (new \DateTimeImmutable('@' . $timestamp))->setTimezone(new \DateTimeZone($timezoneId));

        return match ($pattern) {
            'yyyy-MM-dd HH:mm:ss' => $dt->format('Y-m-d H:i:s'),
            'yyyy-MM-dd' => $dt->format('Y-m-d'),
            'MMMM' => self::deMonth((int) $dt->format('n')),
            'MMMM yyyy' => self::deMonth((int) $dt->format('n')) . ' ' . $dt->format('Y'),
            'EE, dd. MMMM yyyy' => self::deWeekdayShort((int) $dt->format('N'))
                . ', ' . $dt->format('j') . '. ' . self::deMonth((int) $dt->format('n')) . ' ' . $dt->format('Y'),
            "EEEE, 'den' dd. MMMM yyyy" => self::deWeekdayLong((int) $dt->format('N'))
                . ', den ' . $dt->format('j') . '. ' . self::deMonth((int) $dt->format('n')) . ' ' . $dt->format('Y'),
            'EEEE dd. MMMM yyyy' => self::deWeekdayLong((int) $dt->format('N'))
                . ' ' . $dt->format('j') . '. ' . self::deMonth((int) $dt->format('n')) . ' ' . $dt->format('Y'),
            'EEEE' => self::deWeekdayLong((int) $dt->format('N')),
            'HH:mm Uhr', "HH:mm 'Uhr'" => $dt->format('H:i') . ' Uhr',
            default => $dt->format('Y-m-d H:i:s'),
        };
    }

    private static function deMonth(int $month): string
    {
        return [
            1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April', 5 => 'Mai', 6 => 'Juni',
            7 => 'Juli', 8 => 'August', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
        ][$month] ?? '';
    }

    private static function deWeekdayShort(int $nIso): string
    {
        return [1 => 'Mo', 2 => 'Di', 3 => 'Mi', 4 => 'Do', 5 => 'Fr', 6 => 'Sa', 7 => 'So'][$nIso] ?? '';
    }

    private static function deWeekdayLong(int $nIso): string
    {
        return [
            1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag',
            5 => 'Freitag', 6 => 'Samstag', 7 => 'Sonntag',
        ][$nIso] ?? '';
    }
}
