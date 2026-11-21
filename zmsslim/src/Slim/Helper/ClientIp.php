<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

class ClientIp
{
    /**
     * Address of the client the trusted gateway accepted.
     * A caller-supplied forwarding header is ignored unless the socket peer is a trusted proxy.
     * The value used from that header is the last address, which the gateway appends from the connection it accepted.
     */
    public static function getClientIp(): string
    {
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : null;
        $forwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && is_string($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : null;

        return self::resolve($remoteAddr, $forwardedFor) ?? '127.0.0.1';
    }

    public static function resolve(?string $remoteAddr, ?string $forwardedFor): ?string
    {
        $remote = self::validIp($remoteAddr);
        if ($remote === null) {
            return null;
        }

        if (!self::isTrustedProxy($remote)) {
            return $remote;
        }

        return self::appendedByTrustedProxy($remote, $forwardedFor);
    }

    public static function appendedByTrustedProxy(string $remoteAddr, ?string $forwardedFor): string
    {
        return self::lastForwardedIp($forwardedFor) ?? $remoteAddr;
    }

    public static function isTrustedProxy(string $ip): bool
    {
        if (!self::validIp($ip)) {
            return false;
        }

        if (self::matchesTrustedList($ip)) {
            return true;
        }

        if ($ip === '::1' || str_starts_with($ip, '127.')) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE
        ) === false;
    }

    private static function lastForwardedIp(?string $forwardedFor): ?string
    {
        if ($forwardedFor === null || $forwardedFor === '') {
            return null;
        }

        $parts = array_map('trim', explode(',', $forwardedFor));
        $last = end($parts);

        return is_string($last) ? self::validIp($last) : null;
    }

    private static function matchesTrustedList(string $ip): bool
    {
        $configured = getenv('TRUSTED_PROXIES');
        if (!is_string($configured) || $configured === '') {
            return false;
        }

        foreach (array_map('trim', explode(',', $configured)) as $entry) {
            if ($entry === '') {
                continue;
            }
            if (self::ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private static function ipMatches(string $ip, string $entry): bool
    {
        if (!str_contains($entry, '/')) {
            return $ip === $entry;
        }

        [$network, $bits] = explode('/', $entry, 2);
        if (!is_string($network) || !ctype_digit($bits)) {
            return false;
        }

        $ipLong = ip2long($ip);
        $networkLong = ip2long($network);
        $prefix = (int) $bits;
        if ($ipLong === false || $networkLong === false || $prefix < 0 || $prefix > 32) {
            return false;
        }

        $ipLong = (int) sprintf('%u', $ipLong);
        $networkLong = (int) sprintf('%u', $networkLong);
        $mask = $prefix === 0 ? 0 : ((0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF);

        return ($ipLong & $mask) === ($networkLong & $mask);
    }

    private static function validIp(?string $ip): ?string
    {
        if ($ip === null) {
            return null;
        }

        $ip = trim($ip);

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
    }
}
