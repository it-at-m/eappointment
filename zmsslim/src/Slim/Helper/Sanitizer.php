<?php

namespace BO\Slim\Helper;

final class Sanitizer
{
    public static function sanitizeStackTrace(string $trace): string
    {
        $trace = self::applyCatchAllPatterns($trace);

        return self::applySpecificPatterns($trace);
    }

    protected static function applyCatchAllPatterns(string $trace): string
    {
        $trace = self::replace('/mysql:dbname=[^;]+;host=[^;]+;port=\d+/', 'mysql:dbname=***;host=***;port=***', $trace);
        $trace = self::replace('/sqlite:[^;]+/', 'sqlite:***', $trace);

        $trace = self::replace('/[^:\s]+:[^@\s]+@[^:\s]+:\d+/', '***:***@***:***', $trace);
        $trace = self::replace('/[^:\s]+:[^@\s]+@[^:\s]+/', '***:***@***', $trace);

        $trace = self::replace('/port=\d+/', 'port=***', $trace);
        $trace = self::replace('/:\d+(\/|$)/', ':***$1', $trace);

        $trace = self::replace('/@[\d\.]+/', '@***', $trace); // IP addresses
        $trace = self::replace('/@[a-zA-Z0-9\-\.]+/', '@***', $trace); // Hostnames
        $trace = self::replace('/host=[a-zA-Z0-9\-\.]+/', 'host=***', $trace); // Hostnames in connection strings
        $trace = self::replace('/host=\d+\.\d+\.\d+\.\d+/', 'host=***', $trace); // IP addresses in connection strings

        $trace = self::replace('/dbname=[a-zA-Z0-9\-_]+/', 'dbname=***', $trace);
        $trace = self::replace('/database \'[a-zA-Z0-9\-_]+\'/', 'database \'***\'', $trace);

        $trace = self::replace('/user=\'[^\']+\'/', 'user=\'***\'', $trace);
        $trace = self::replace('/user=[^;]+/', 'user=***', $trace);

        $trace = self::replace('/password=\'[^\']+\'/', 'password=\'***\'', $trace);
        $trace = self::replace('/password=[^;]+/', 'password=***', $trace);

        $trace = self::replace('/Access denied for user \'[^\']+\'@\'[^\']+\'/', 'Access denied for user \'***\'@\'***\'', $trace);
        $trace = self::replace('/Access denied for user [^@]+@[^\s]+/', 'Access denied for user ***@***', $trace);

        return $trace;
    }

    protected static function applySpecificPatterns(string $trace): string
    {
        if (defined('\App::DB_PASSWORD')) {
            $trace = self::replaceQuoted(\App::DB_PASSWORD, $trace);
        }
        if (defined('\App::DB_USER')) {
            $trace = self::replaceQuoted(\App::DB_USER, $trace);
        }
        if (defined('\App::DB_HOST')) {
            $trace = self::replaceQuoted(\App::DB_HOST, $trace);
        }
        if (defined('\App::DB_NAME')) {
            $trace = self::replaceQuoted(\App::DB_NAME, $trace);
        }
        if (defined('\App::DB_PORT')) {
            $port = \App::DB_PORT;
            if (is_int($port)) {
                $port = (string) $port;
            }
            if (is_string($port) && $port !== '') {
                $encodedPort = preg_quote($port, '/');
                $trace = self::replace('/' . $encodedPort . '/', '***', $trace);
                $trace = self::replace('/' . preg_quote(urlencode($port), '/') . '/', '***', $trace);
                $trace = self::replace('/\'' . $encodedPort . '\'/', '\'***\'', $trace);
                $trace = self::replace('/port=' . $encodedPort . '/', 'port=***', $trace);
                $trace = self::replace('/port=' . preg_quote(urlencode($port), '/') . '/', 'port=***', $trace);
            }
        }

        return $trace;
    }

    protected static function replaceQuoted(mixed $value, string $trace): string
    {
        if (!is_string($value) || $value === '') {
            return $trace;
        }
        $encoded = preg_quote($value, '/');
        $trace = self::replace('/' . $encoded . '/', '***', $trace);
        $trace = self::replace('/' . preg_quote(urlencode($value), '/') . '/', '***', $trace);
        $trace = self::replace('/\'' . $encoded . '\'/', '\'***\'', $trace);

        return $trace;
    }

    protected static function replace(string $pattern, string $replacement, string $subject): string
    {
        if ($pattern === '') {
            return $subject;
        }
        return preg_replace($pattern, $replacement, $subject) ?? $subject;
    }
}
