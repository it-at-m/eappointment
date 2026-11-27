<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Utils;

class IpList
{
    private const int IPV4_BITS = 32;
    private const int IPV6_BITS = 128;

    public static function contains(string $ip, string $ipList): bool
    {
        foreach (self::parse($ipList) as $range) {
            if (self::isIpInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function parse(string $ipList): array
    {
        if ($ipList === '') {
            return [];
        }

        $entries = [];
        foreach (array_map('trim', explode(',', $ipList)) as $entry) {
            if ($entry !== '' && self::isValidEntry($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private static function isValidEntry(string $entry): bool
    {
        if (strpos($entry, '/') !== false) {
            [$ip, $bits] = explode('/', $entry, 2);
            return filter_var($ip, FILTER_VALIDATE_IP) !== false
                && is_numeric($bits)
                && (int) $bits >= 0
                && (int) $bits <= (strpos($ip, ':') !== false ? self::IPV6_BITS : self::IPV4_BITS);
        }

        return filter_var($entry, FILTER_VALIDATE_IP) !== false;
    }

    private static function isIpInRange(string $ip, string $range): bool
    {
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        if (filter_var($ip, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }

        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range, 2);
        if (filter_var($subnet, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if (
            $ipBin === false || $subnetBin === false
            || strlen($ipBin) !== strlen($subnetBin)
        ) {
            return false;
        }

        $bitCount = (int) $bits;
        $maxBits = strlen($ipBin) === 4 ? self::IPV4_BITS : self::IPV6_BITS;
        if ($bitCount < 0 || $bitCount > $maxBits) {
            return false;
        }

        $mask = str_repeat("\xFF", intdiv($bitCount, 8));
        if ($bitCount % 8 !== 0) {
            $mask .= chr(0xFF << (8 - ($bitCount % 8)));
        }
        $mask = str_pad($mask, strlen($ipBin), "\x00");

        return ($ipBin & $mask) === ($subnetBin & $mask);
    }
}
