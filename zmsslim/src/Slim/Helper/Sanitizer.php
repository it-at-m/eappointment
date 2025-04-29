<?php

namespace BO\Slim\Helper;

class Sanitizer
{
    public static function sanitizeStackTrace($trace)
    {
        $trace = self::applyCatchAllPatterns($trace);

        $trace = self::applySpecificPatterns($trace);

        return $trace;
    }

    protected static function applyCatchAllPatterns($trace)
    {
        $trace = preg_replace('/mysql:dbname=[^;]+;host=[^;]+;port=\d+/', 'mysql:dbname=***;host=***;port=***', $trace);
        $trace = preg_replace('/sqlite:[^;]+/', 'sqlite:***', $trace);

        $trace = preg_replace('/[^:\s]+:[^@\s]+@[^:\s]+:\d+/', '***:***@***:***', $trace);
        $trace = preg_replace('/[^:\s]+:[^@\s]+@[^:\s]+/', '***:***@***', $trace);

        $trace = preg_replace('/port=\d+/', 'port=***', $trace);
        $trace = preg_replace('/:\d+(\/|$)/', ':***$1', $trace);

        $trace = preg_replace('/@[\d\.]+/', '@***', $trace); // IP addresses
        $trace = preg_replace('/@[a-zA-Z0-9\-\.]+/', '@***', $trace); // Hostnames
        $trace = preg_replace('/host=[a-zA-Z0-9\-\.]+/', 'host=***', $trace); // Hostnames in connection strings
        $trace = preg_replace('/host=\d+\.\d+\.\d+\.\d+/', 'host=***', $trace); // IP addresses in connection strings

        $trace = preg_replace('/dbname=[a-zA-Z0-9\-_]+/', 'dbname=***', $trace);
        $trace = preg_replace('/database \'[a-zA-Z0-9\-_]+\'/', 'database \'***\'', $trace);

        $trace = preg_replace('/user=\'[^\']+\'/', 'user=\'***\'', $trace);
        $trace = preg_replace('/user=[^;]+/', 'user=***', $trace);

        $trace = preg_replace('/password=\'[^\']+\'/', 'password=\'***\'', $trace);
        $trace = preg_replace('/password=[^;]+/', 'password=***', $trace);

        $trace = preg_replace('/Access denied for user \'[^\']+\'@\'[^\']+\'/', 'Access denied for user \'***\'@\'***\'', $trace);
        $trace = preg_replace('/Access denied for user [^@]+@[^\s]+/', 'Access denied for user ***@***', $trace);

        return $trace;
    }

    protected static function applySpecificPatterns($trace)
    {
        if (defined('\App::DB_PASSWORD')) {
            $password = \App::DB_PASSWORD;
            $encodedPassword = preg_quote($password, '/');
            $trace = preg_replace('/' . $encodedPassword . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($password), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($password, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_USER')) {
            $user = \App::DB_USER;
            $encodedUser = preg_quote($user, '/');
            $trace = preg_replace('/' . $encodedUser . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($user), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($user, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_HOST')) {
            $host = \App::DB_HOST;
            $encodedHost = preg_quote($host, '/');
            $trace = preg_replace('/' . $encodedHost . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($host), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($host, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_NAME')) {
            $dbname = \App::DB_NAME;
            $encodedDbname = preg_quote($dbname, '/');
            $trace = preg_replace('/' . $encodedDbname . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($dbname), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($dbname, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_PORT')) {
            $port = \App::DB_PORT;
            $encodedPort = preg_quote($port, '/');
            $trace = preg_replace('/' . $encodedPort . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($port), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($port, '/') . '\'/', '\'***\'', $trace);
            $trace = preg_replace('/port=' . $encodedPort . '/', 'port=***', $trace);
            $trace = preg_replace('/port=' . preg_quote(urlencode($port), '/') . '/', 'port=***', $trace);
        }

        return $trace;
    }
}
